<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Controller;

use Laminas\Form\FormInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Stdlib\ResponseInterface as Response;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use LaminasFriends\Mvc\User\Controller\Plugin\UserAuthenticationPlugin;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\Service\UserService;
use LaminasFriends\Mvc\User\Options\UserControllerOptionsInterface;

/**
 * Class UserController
 * @method UserAuthenticationPlugin mvcUserAuthentication()
 * @method FlashMessenger flashMessenger()
 */
class UserController extends AbstractActionController
{
    protected UserService $userService;
    protected FormInterface $loginForm;
    protected FormInterface $registerForm;
    protected FormInterface $changePasswordForm;
    protected FormInterface $changeEmailForm;
    protected UserControllerOptionsInterface $options;
    /**
     * @var callable $redirectCallback
     */
    protected $redirectCallback;
    /**
     * @todo Make this dynamic / translation-friendly
     */
    protected string $failedLoginMessage = 'Authentication failed. Please try again.';

    /**
     * UserController constructor.
     *
     * @param UserControllerOptionsInterface $options
     * @param UserService                    $userService
     * @param callable                       $redirectCallback
     * @param FormInterface                  $loginForm
     * @param FormInterface                  $registerForm
     * @param FormInterface                  $changePasswordForm
     * @param FormInterface                  $changeEmailForm
     */
    public function __construct(
        UserControllerOptionsInterface $options,
        UserService $userService,
        callable $redirectCallback,
        FormInterface $loginForm,
        FormInterface $registerForm,
        FormInterface $changePasswordForm,
        FormInterface $changeEmailForm
    ) {
        $this->options = $options;
        $this->userService = $userService;
        $this->redirectCallback = $redirectCallback;
        $this->loginForm = $loginForm;
        $this->registerForm = $registerForm;
        $this->changePasswordForm = $changePasswordForm;
        $this->changeEmailForm = $changeEmailForm;
    }

    /**
     * UserService page
     */
    public function indexAction()
    {
        if (!$this->mvcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute(Module::ROUTE_LOGIN);
        }
        return new ViewModel();
    }

    /**
     * LoginForm form
     */
    public function loginAction()
    {
        if ($this->mvcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute($this->options->getLoginRedirectRoute());
        }

        if ($this->options->getUseRedirectParameterIfPresent() && $this->getRequest()->getQuery()->get('redirect')) {
            $redirect = $this->getRequest()->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if (!$this->getRequest()->isPost()) {
            return [
                'loginForm' => $this->loginForm,
                'redirect'  => $redirect,
                'enableRegistration' => $this->options->getEnableRegistration(),
            ];
        }

        $this->loginForm->setData($this->getRequest()->getPost());

        if (!$this->loginForm->isValid()) {
            $this->flashMessenger()->setNamespace('mvcuser-login-form')->addMessage($this->failedLoginMessage);
            return $this->redirect()->toUrl($this->url()->fromRoute(Module::ROUTE_LOGIN).($redirect ? '?redirect='. rawurlencode($redirect) : ''));
        }

        // clear adapters
        $this->mvcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->mvcUserAuthentication()->getAuthService()->clearIdentity();

        return $this->forward()->dispatch(Module::CONTROLLER_NAME, ['action' => 'authenticate']);
    }

    /**
     * Logout and clear the identity
     */
    public function logoutAction()
    {
        $this->mvcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->mvcUserAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->mvcUserAuthentication()->getAuthService()->clearIdentity();

        return ($this->redirectCallback)();
    }

    /**
     * General-purpose authentication action
     */
    public function authenticateAction()
    {
        if ($this->mvcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute($this->options->getLoginRedirectRoute());
        }

        $adapter = $this->mvcUserAuthentication()->getAuthAdapter();
        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        $result = $adapter->prepareForAuthentication($this->getRequest());

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return $result;
        }

        $auth = $this->mvcUserAuthentication()->getAuthService()->authenticate($adapter);

        if (!$auth->isValid()) {
            $this->flashMessenger()->setNamespace('mvcuser-login-form')->addMessage($this->failedLoginMessage);
            $adapter->resetAdapters();
            return $this->redirect()->toUrl(
                $this->url()->fromRoute(Module::ROUTE_LOGIN) .
                ($redirect ? '?redirect='. rawurlencode($redirect) : '')
            );
        }

        $redirect = $this->redirectCallback;

        return $redirect();
    }

    /**
     * RegisterForm new user
     */
    public function registerAction()
    {
        // if the user is logged in, we don't need to register
        if ($this->mvcUserAuthentication()->hasIdentity()) {
            // redirect to the login redirect route
            return $this->redirect()->toRoute($this->options->getLoginRedirectRoute());
        }
        // if registration is disabled
        if (!$this->options->getEnableRegistration()) {
            return ['enableRegistration' => false];
        }

        if ($this->options->getUseRedirectParameterIfPresent() && $this->getRequest()->getQuery()->get('redirect')) {
            $redirect = $this->getRequest()->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        $redirectUrl = $this->url()->fromRoute(Module::ROUTE_REGISTER)
            . ($redirect ? '?redirect=' . rawurlencode($redirect) : '');
        $prg = $this->prg($redirectUrl, true);

        if ($prg instanceof Response) {
            return $prg;
        }

        if ($prg === false) {
            return [
                'registerForm' => $this->registerForm,
                'enableRegistration' => $this->options->getEnableRegistration(),
                'redirect' => $redirect,
            ];
        }
        $redirect = $prg['redirect'] ?? null;
        $post = $prg;

        $class = $this->options->getUserEntityClass();
        $this->registerForm->bind(new $class());
        $this->registerForm->setData($post);

        if (!$this->registerForm->isValid()) {
            return [
                'registerForm' => $this->registerForm,
                'enableRegistration' => $this->options->getEnableRegistration(),
                'redirect' => $redirect,
            ];
        }
        /* @var $user UserEntityInterface */
        $user = $this->registerForm->getData();

        if ($this->options->getEnableUsername()) {
            $user->setUsername($post['username']);
        }
        if ($this->options->getEnableDisplayName()) {
            $user->setDisplayName($post['display_name']);
        }

        // If user state is enabled, set the default state value
        if ($this->options->getEnableUserState()) {
            $user->setState($this->options->getDefaultUserState());
        }

        $user = $this->userService->register($user);

        $redirect = $prg['redirect'] ?? null;

        if (!$user) {
            return [
                'registerForm' => $this->registerForm,
                'enableRegistration' => $this->options->getEnableRegistration(),
                'redirect' => $redirect,
            ];
        }

        if ($this->userService->getOptions()->getLoginAfterRegistration()) {
            $identityFields = $this->userService->getOptions()->getAuthIdentityFields();
            if (in_array('email', $identityFields)) {
                $post['identity'] = $user->getEmail();
            } elseif (in_array('username', $identityFields)) {
                $post['identity'] = $user->getUsername();
            }
            $post['credential'] = $post['password'];
            $this->getRequest()->setPost(new Parameters($post));
            return $this->forward()->dispatch(Module::CONTROLLER_NAME, ['action' => 'authenticate']);
        }

        // TODO: Add the redirect parameter here...
        return $this->redirect()->toUrl($this->url()->fromRoute(Module::ROUTE_LOGIN) . ($redirect ? '?redirect='. rawurlencode($redirect) : ''));
    }

    /**
     * Change the users password
     */
    public function changepasswordAction()
    {
        // if the user isn't logged in, we can't change password
        if (!$this->mvcUserAuthentication()->hasIdentity()) {
            // redirect to the login redirect route
            return $this->redirect()->toRoute($this->options->getLoginRedirectRoute());
        }

        $prg = $this->prg(Module::ROUTE_CHANGEPASSWD);

        $fm = $this->flashMessenger()->setNamespace('change-password')->getMessages();
        $status = $fm[0] ?? null;

        if ($prg instanceof Response) {
            return $prg;
        }

        if ($prg === false) {
            return [
                'status' => $status,
                'changePasswordForm' => $this->changePasswordForm,
            ];
        }

        $this->changePasswordForm->setData($prg);

        if (!$this->changePasswordForm->isValid()) {
            return [
                'status' => false,
                'changePasswordForm' => $this->changePasswordForm,
            ];
        }

        if (!$this->userService->changePassword($this->changePasswordForm->getData())) {
            return [
                'status' => false,
                'changePasswordForm' => $this->changePasswordForm,
            ];
        }

        $this->flashMessenger()->setNamespace('change-password')->addMessage(true);
        return $this->redirect()->toRoute(Module::ROUTE_CHANGEPASSWD);
    }

    public function changeEmailAction()
    {
        // if the user isn't logged in, we can't change email
        if (!$this->mvcUserAuthentication()->hasIdentity()) {
            // redirect to the login redirect route
            return $this->redirect()->toRoute($this->options->getLoginRedirectRoute());
        }

        $request = $this->getRequest();
        $request->getPost()->set('identity', $this->userService->getAuthService()->getIdentity()->getEmail());

        $fm = $this->flashMessenger()->setNamespace('change-email')->getMessages();
        $status = $fm[0] ?? null;

        $prg = $this->prg(Module::ROUTE_CHANGEEMAIL);
        if ($prg instanceof Response) {
            return $prg;
        }

        if ($prg === false) {
            return [
                'status' => $status,
                'changeEmailForm' => $this->changeEmailForm,
            ];
        }

        $this->changeEmailForm->setData($prg);

        if (!$this->changeEmailForm->isValid()) {
            return [
                'status' => false,
                'changeEmailForm' => $this->changeEmailForm,
            ];
        }

        $change = $this->userService->changeEmail($prg);

        if (!$change) {
            $this->flashMessenger()->setNamespace('change-email')->addMessage(false);
            return [
                'status' => false,
                'changeEmailForm' => $this->changeEmailForm,
            ];
        }

        $this->flashMessenger()->setNamespace('change-email')->addMessage(true);
        return $this->redirect()->toRoute(Module::ROUTE_CHANGEEMAIL);
    }
}
