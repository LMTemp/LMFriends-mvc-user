<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Forward;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Plugin\Prg\PostRedirectGet;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;
use Laminas\Form\FormElementManager;
use LaminasFriends\Mvc\User\Controller\RedirectCallback;
use LaminasFriends\Mvc\User\Controller\UserController as Controller;
use Laminas\Http\Response;
use Laminas\Stdlib\Parameters;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Service\UserService;
use Laminas\Form\Form;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Entity\UserEntity as UserIdentity;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain;
use LaminasFriends\Mvc\User\Form\LoginForm;
use LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication;

class UserControllerTest extends TestCase
{
    /**
     * @var Controller $controller
     */
    protected $controller;

    protected $pluginManager;

    public $pluginManagerPlugins = [];

    protected $zfcUserAuthenticationPlugin;

    protected $options;

    /**
     * @var MockObject|RedirectCallback
     */
    protected $redirectCallback;

    protected function setUp(): void
    {
        $this->redirectCallback = $this->getMockBuilder(RedirectCallback::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = new Controller($this->redirectCallback);
        $this->controller = $controller;

        $this->zfcUserAuthenticationPlugin = $this->createMock(ZfcUserAuthentication::class);

        $pluginManager = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginManager
            ->method('get')
            ->willReturnCallback([$this, 'helperMockCallbackPluginManagerGet']);

        $this->pluginManager = $pluginManager;

        $options = $this->createMock(ModuleOptions::class);
        $this->options = $options;

        $controller->setPluginManager($pluginManager);
        $controller->setOptions($options);
    }

    public function setUpZfcUserAuthenticationPlugin($option)
    {
        if (array_key_exists('hasIdentity', $option)) {
            $return = (is_callable($option['hasIdentity']))
                ? static::returnCallback($option['hasIdentity'])
                : static::returnValue($option['hasIdentity']);
            $this->zfcUserAuthenticationPlugin
                ->method('hasIdentity')
                ->will($return);
        }

        if (array_key_exists('getAuthAdapter', $option)) {
            $return = (is_callable($option['getAuthAdapter']))
                ? static::returnCallback($option['getAuthAdapter'])
                : static::returnValue($option['getAuthAdapter']);

            $this->zfcUserAuthenticationPlugin
                ->method('getAuthAdapter')
                ->will($return);
        }

        if (array_key_exists('getAuthService', $option)) {
            $return = (is_callable($option['getAuthService']))
                ? static::returnCallback($option['getAuthService'])
                : static::returnValue($option['getAuthService']);

            $this->zfcUserAuthenticationPlugin
                ->method('getAuthService')
                ->will($return);
        }

        $this->pluginManagerPlugins['zfcUserAuthentication'] = $this->zfcUserAuthenticationPlugin;

        return $this->zfcUserAuthenticationPlugin;
    }

    /**
     * @dataProvider providerTestActionControllHasIdentity
     */
    public function testActionControllHasIdentity($methodeName, $hasIdentity, $redirectRoute, $optionGetter)
    {
        $controller = $this->controller;
        $redirectRoute = $redirectRoute ?: $controller::ROUTE_LOGIN;

        $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>$hasIdentity
            ]
        );

        $response = new Response();

        if ($optionGetter) {
            $this->options->expects(static::once())
                ->method($optionGetter)
                ->willReturn($redirectRoute);
        }

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects(static::once())
            ->method('toRoute')
            ->with($redirectRoute)
            ->willReturn($response);

        $this->pluginManagerPlugins['redirect']= $redirect;

        $result = $controller->$methodeName();

        static::assertInstanceOf(Response::class, $result);
        static::assertSame($response, $result);
    }

    /**
     * @depend testActionControllHasIdentity
     */
    public function testIndexActionLoggedIn()
    {
        $controller = $this->controller;
        $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>true
            ]
        );

        $result = $controller->indexAction();

        static::assertInstanceOf(ViewModel::class, $result);
    }


    /**
     * @dataProvider providerTrueOrFalseX2
     * @depend testActionControllHasIdentity
     */
    public function testLoginActionValidFormRedirectFalse($isValid, $wantRedirect)
    {
        $controller = $this->controller;
        $redirectUrl = 'localhost/redirect1';

        $plugin = $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>false
            ]
        );

        $flashMessenger = $this->createMock(
            FlashMessenger::class
        );
        $this->pluginManagerPlugins['flashMessenger']= $flashMessenger;

        $flashMessenger
            ->method('setNamespace')
            ->with('zfcuser-login-form')
            ->will(static::returnSelf());

        $flashMessenger
            ->method('addMessage')
            ->will(static::returnSelf());

        $postArray = ['some', 'data'];
        $request = $this->createMock(Request::class);
        $request
            ->method('isPost')
            ->willReturn(true);
        $request
            ->method('getPost')
            ->willReturn($postArray);

        $this->helperMakePropertyAccessable($controller, 'request', $request);

        $form = $this->getMockBuilder(LoginForm::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form
            ->method('isValid')
            ->willReturn((bool)$isValid);


        $this->options
            ->method('getUseRedirectParameterIfPresent')
            ->willReturn((bool)$wantRedirect);
        if ($wantRedirect) {
            $params = new Parameters();
            $params->set('redirect', $redirectUrl);

            $request
                ->method('getQuery')
                ->willReturn($params);
        }

        if ($isValid) {
            $adapter = $this->createMock(AdapterChain::class);
            $adapter->expects(static::once())
                ->method('resetAdapters');

            $service = $this->createMock(AuthenticationService::class);
            $service->expects(static::once())
                ->method('clearIdentity');

            $plugin = $this->setUpZfcUserAuthenticationPlugin(
                [
                'getAuthAdapter'=>$adapter,
                'getAuthService'=>$service
                ]
            );

            $form->expects(static::once())
                ->method('setData')
                ->with($postArray);

            $expectedResult = new stdClass();

            $forwardPlugin = $this->getMockBuilder(Forward::class)
                ->disableOriginalConstructor()
                ->getMock();
            $forwardPlugin->expects(static::once())
                ->method('dispatch')
                ->with($controller::CONTROLLER_NAME, ['action' => 'authenticate'])
                ->willReturn($expectedResult);

            $this->pluginManagerPlugins['forward']= $forwardPlugin;
        } else {
            $response = new Response();

            $redirectQuery = $wantRedirect ? '?redirect='. rawurlencode($redirectUrl) : '';
            $route_url = '/user/login';


            $redirect = $this->createMock(Redirect::class, ['toUrl']);
            $redirect
                ->method('toUrl')
                ->with($route_url . $redirectQuery)
                ->willReturnCallback(
                    static function ($url) use (&$response) {
                        $response->getHeaders()->addHeaderLine('Location', $url);
                        $response->setStatusCode(302);

                        return $response;
                    }
                );

            $this->pluginManagerPlugins['redirect']= $redirect;


            $response = new Response();
            $url = $this->createMock(Url::class, ['fromRoute']);
            $url->expects(static::once())
                ->method('fromRoute')
                ->with($controller::ROUTE_LOGIN)
                ->willReturn($route_url);

            $this->pluginManagerPlugins['url']= $url;
            $TEST = true;
        }


        $controller->setLoginForm($form);
        $result = $controller->loginAction();

        if ($isValid) {
            static::assertSame($expectedResult, $result);
        } else {
            static::assertInstanceOf(Response::class, $result);
            static::assertEquals($response, $result);
            static::assertEquals($route_url . $redirectQuery, $result->getHeaders()->get('Location')->getFieldValue());
        }
    }

    /**
     * @dataProvider providerTrueOrFalse
     * @depend testActionControllHasIdentity
     */
    public function testLoginActionIsNotPost($redirect)
    {
        $plugin = $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>false
            ]
        );

        $flashMessenger = $this->createMock(FlashMessenger::class);

        $this->pluginManagerPlugins['flashMessenger'] = $flashMessenger;

        $request = $this->createMock(Request::class);
        $request->expects(static::once())
            ->method('isPost')
            ->willReturn(false);

        $form = $this->getMockBuilder(LoginForm::class)
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects(static::never())
            ->method('isValid');

        $this->options
            ->method('getUseRedirectParameterIfPresent')
            ->willReturn((bool)$redirect);
        if ($redirect) {
            $params = new Parameters();
            $params->set('redirect', 'http://localhost/');

            $request
                ->method('getQuery')
                ->willReturn($params);
        }

        $this->helperMakePropertyAccessable($this->controller, 'request', $request);

        $this->controller->setLoginForm($form);
        $result = $this->controller->loginAction();

        static::assertArrayHasKey('loginForm', $result);
        static::assertArrayHasKey('redirect', $result);
        static::assertArrayHasKey('enableRegistration', $result);

        static::assertInstanceOf(LoginForm::class, $result['loginForm']);
        static::assertSame($form, $result['loginForm']);

        if ($redirect) {
            static::assertEquals('http://localhost/', $result['redirect']);
        } else {
            static::assertFalse($result['redirect']);
        }

        static::assertEquals($this->options->getEnableRegistration(), $result['enableRegistration']);
    }


    /**
     * @dataProvider providerRedirectPostQueryMatrix
     * @depend testActionControllHasIdentity
     */
    public function testLogoutAction($withRedirect, $post, $query)
    {
        $controller = $this->controller;

        $adapter = $this->createMock(AdapterChain::class);
        $adapter->expects(static::once())
            ->method('resetAdapters');

        $adapter->expects(static::once())
            ->method('logoutAdapters');

        $service = $this->createMock(AuthenticationService::class);
        $service->expects(static::once())
            ->method('clearIdentity');

        $this->setUpZfcUserAuthenticationPlugin(
            [
            'getAuthAdapter'=>$adapter,
            'getAuthService'=>$service
            ]
        );


        $response = new Response();

        $this->redirectCallback->expects(static::once())
            ->method('__invoke')
            ->willReturn($response);

        $result = $controller->logoutAction();

        static::assertInstanceOf(Response::class, $result);
        static::assertSame($response, $result);
    }

    public function testLoginRedirectFailsWithUrl()
    {

    }

    /**
     * @dataProvider providerTestAuthenticateAction
     * @depend testActionControllHasIdentity
     */
    public function testAuthenticateAction($wantRedirect, $post, $query, $prepareResult = false, $authValid = false)
    {
        $controller = $this->controller;
        $response = new Response();
        $hasRedirect = !($query === null && $post === null);

        $params = $this->createMock(Params::class);
        $params
            ->method('__invoke')
            ->will(static::returnSelf());
        $params->expects(static::once())
            ->method('fromPost')
            ->willReturnCallback(
                static function ($key, $default) use ($post) {
                    return $post ?: $default;
                }
            );
        $params->expects(static::once())
            ->method('fromQuery')
            ->willReturnCallback(
                static function ($key, $default) use ($query) {
                    return $query ?: $default;
                }
            );
        $this->pluginManagerPlugins['params'] = $params;


        $request = $this->createMock(Request::class);
        $this->helperMakePropertyAccessable($controller, 'request', $request);


        $adapter = $this->createMock(AdapterChain::class);
        $adapter->expects(static::once())
            ->method('prepareForAuthentication')
            ->with($request)
            ->willReturn($prepareResult);

        $service = $this->createMock(AuthenticationService::class);


        $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>false,
            'getAuthAdapter'=>$adapter,
            'getAuthService'=>$service
            ]
        );

        if (is_bool($prepareResult)) {
            $authResult = $this->getMockBuilder(Result::class)
                ->disableOriginalConstructor()
                ->getMock();
            $authResult->expects(static::once())
                ->method('isValid')
                ->willReturn($authValid);

            $service->expects(static::once())
                ->method('authenticate')
                ->with($adapter)
                ->willReturn($authResult);

            $redirect = $this->createMock(Redirect::class);
            $this->pluginManagerPlugins['redirect'] = $redirect;

            if ($authValid) {
                $this->redirectCallback->expects(static::once())
                    ->method('__invoke');
            } else {
                $flashMessenger = $this->createMock(
                    FlashMessenger::class
                );
                $this->pluginManagerPlugins['flashMessenger']= $flashMessenger;

                $flashMessenger->expects(static::once())
                    ->method('setNamespace')
                    ->with('zfcuser-login-form')
                    ->will(static::returnSelf());

                $flashMessenger->expects(static::once())
                    ->method('addMessage');

                $adapter->expects(static::once())
                    ->method('resetAdapters');

                $redirectQuery = ($post ?: $query ?: false);
                $redirectQuery = $redirectQuery ? '?redirect=' . rawurlencode($redirectQuery) : '';

                $redirect->expects(static::once())
                    ->method('toUrl')
                    ->with('user/login' . $redirectQuery)
                    ->willReturn($response);

                $url = $this->createMock(Url::class);
                $url->expects(static::once())
                    ->method('fromRoute')
                    ->with($controller::ROUTE_LOGIN)
                    ->willReturn('user/login');
                $this->pluginManagerPlugins['url'] = $url;
            }

            $this->options
                ->method('getUseRedirectParameterIfPresent')
                ->willReturn((bool)$wantRedirect);
        }

        $result = $controller->authenticateAction();


    }

    /**
     *
     * @depend testActionControllHasIdentity
     */
    public function testRegisterActionIsNotAllowed()
    {
        $controller = $this->controller;

        $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>false
            ]
        );

        $this->options->expects(static::once())
            ->method('getEnableRegistration')
            ->willReturn(false);

        $result = $controller->registerAction();

        static::assertIsArray($result);
        static::assertArrayHasKey('enableRegistration', $result);
        static::assertFalse($result['enableRegistration']);
    }

    /**
     *
     * @dataProvider providerTestRegisterAction
     * @depend testActionControllHasIdentity
     * @depend testRegisterActionIsNotAllowed
     */
    public function testRegisterAction($wantRedirect, $postRedirectGetReturn, $registerSuccess, $loginAfterSuccessWith)
    {
        $controller = $this->controller;
        $redirectUrl = 'localhost/redirect1';
        $route_url = '/user/register';
        $expectedResult = null;

        $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>false
            ]
        );

        $this->options
            ->method('getEnableRegistration')
            ->willReturn(true);

        $request = $this->createMock(Request::class);
        $this->helperMakePropertyAccessable($controller, 'request', $request);

        $userService = $this->createMock(UserService::class);
        $controller->setUserService($userService);

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller->setRegisterForm($form);

        $this->options
            ->method('getUseRedirectParameterIfPresent')
            ->willReturn((bool)$wantRedirect);

        if ($wantRedirect) {
            $params = new Parameters();
            $params->set('redirect', $redirectUrl);

            $request
                ->method('getQuery')
                ->willReturn($params);
        }


        $url = $this->createMock(Url::class);
        $url->expects(static::at(0))
            ->method('fromRoute')
            ->with($controller::ROUTE_REGISTER)
            ->willReturn($route_url);

        $this->pluginManagerPlugins['url']= $url;

        $prg = $this->createMock(PostRedirectGet::class);
        $this->pluginManagerPlugins['prg'] = $prg;

        $redirectQuery = $wantRedirect ? '?redirect=' . rawurlencode($redirectUrl) : '';
        $prg->expects(static::once())
            ->method('__invoke')
            ->with($route_url . $redirectQuery)
            ->willReturn($postRedirectGetReturn);

        if ($registerSuccess) {
            $user = new UserIdentity();
            $user->setEmail('zfc-user@trash-mail.com');
            $user->setUsername('zfc-user');

            $userService->expects(static::once())
                ->method('register')
                ->with($postRedirectGetReturn)
                ->willReturn($user);

            $userService
                ->method('getOptions')
                ->willReturn($this->options);

            $this->options->expects(static::once())
                ->method('getLoginAfterRegistration')
                ->willReturn(!empty($loginAfterSuccessWith));

            if ($loginAfterSuccessWith) {
                $this->options->expects(static::once())
                    ->method('getAuthIdentityFields')
                    ->willReturn([$loginAfterSuccessWith]);


                $expectedResult = new stdClass();
                $forwardPlugin = $this->getMockBuilder(Forward::class)
                    ->disableOriginalConstructor()
                    ->getMock();
                $forwardPlugin->expects(static::once())
                    ->method('dispatch')
                    ->with($controller::CONTROLLER_NAME, ['action' => 'authenticate'])
                    ->willReturn($expectedResult);

                $this->pluginManagerPlugins['forward']= $forwardPlugin;
            } else {
                $response = new Response();
                $route_url = '/user/login';

                $redirectUrl = $postRedirectGetReturn['redirect'] ?? null;

                $redirectQuery = $redirectUrl ? '?redirect='. rawurlencode($redirectUrl) : '';

                $redirect = $this->createMock(Redirect::class);
                $redirect->expects(static::once())
                    ->method('toUrl')
                    ->with($route_url . $redirectQuery)
                    ->willReturn($response);

                $this->pluginManagerPlugins['redirect']= $redirect;


                $url->expects(static::at(1))
                    ->method('fromRoute')
                    ->with($controller::ROUTE_LOGIN)
                    ->willReturn($route_url);
            }
        }

        /***********************************************
         * run
         */
        $result = $controller->registerAction();

        /***********************************************
         * assert
         */
        if ($postRedirectGetReturn instanceof Response) {
            $expectedResult = $postRedirectGetReturn;
        }
        if ($expectedResult) {
            static::assertSame($expectedResult, $result);
            return;
        }

        if ($postRedirectGetReturn === false) {
            $expectedResult = [
                'registerForm' => $form,
                'enableRegistration' => true,
                'redirect' => $wantRedirect ? $redirectUrl : false
            ];
        } elseif ($registerSuccess === false) {
            $expectedResult = [
                'registerForm' => $form,
                'enableRegistration' => true,
                'redirect' => $postRedirectGetReturn['redirect'] ?? null
            ];
        }

        if ($expectedResult) {
            static::assertIsArray($result);
            static::assertArrayHasKey('registerForm', $result);
            static::assertArrayHasKey('enableRegistration', $result);
            static::assertArrayHasKey('redirect', $result);
            static::assertEquals($expectedResult, $result);
        } else {
            static::assertInstanceOf(Response::class, $result);
            static::assertSame($response, $result);
        }
    }


    /**
     * @dataProvider providerTestChangeAction
     * @depend testActionControllHasIdentity
     */
    public function testChangepasswordAction($status, $postRedirectGetReturn, $isValid, $changeSuccess)
    {
        $controller = $this->controller;
        $response = new Response();

        $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>true
            ]
        );

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();


        $controller->setChangePasswordForm($form);


        $flashMessenger = $this->createMock(
            FlashMessenger::class
        );
        $this->pluginManagerPlugins['flashMessenger']= $flashMessenger;

        $flashMessenger
            ->method('setNamespace')
            ->with('change-password')
            ->will(static::returnSelf());

        $flashMessenger->expects(static::once())
            ->method('getMessages')
            ->willReturn($status ? ['test'] : []);


        $prg = $this->createMock(PostRedirectGet::class);
        $this->pluginManagerPlugins['prg'] = $prg;


        $prg->expects(static::once())
            ->method('__invoke')
            ->with($controller::ROUTE_CHANGEPASSWD)
            ->willReturn($postRedirectGetReturn);

        if ($postRedirectGetReturn !== false && !($postRedirectGetReturn instanceof Response)) {
            $form->expects(static::once())
                ->method('setData')
                ->with($postRedirectGetReturn);

            $form->expects(static::once())
                ->method('isValid')
                ->willReturn((bool)$isValid);

            if ($isValid) {
                $userService = $this->createMock(UserService::class);

                $controller->setUserService($userService);

                $form->expects(static::once())
                    ->method('getData')
                    ->willReturn($postRedirectGetReturn);

                $userService->expects(static::once())
                    ->method('changePassword')
                    ->with($postRedirectGetReturn)
                    ->willReturn((bool)$changeSuccess);


                if ($changeSuccess) {
                    $flashMessenger->expects(static::once())
                        ->method('addMessage')
                        ->with(true);


                    $redirect = $this->createMock(Redirect::class);
                    $redirect->expects(static::once())
                        ->method('toRoute')
                        ->with($controller::ROUTE_CHANGEPASSWD)
                        ->willReturn($response);

                    $this->pluginManagerPlugins['redirect']= $redirect;
                }
            }
        }


        $result = $controller->changepasswordAction();
        $exceptedReturn = null;

        if ($postRedirectGetReturn instanceof Response) {
            static::assertInstanceOf(Response::class, $result);
            static::assertSame($postRedirectGetReturn, $result);
        } else {
            if ($postRedirectGetReturn === false) {
                $exceptedReturn = [
                    'status' => $status ? 'test' : null,
                    'changePasswordForm' => $form,
                ];
            } elseif ($isValid === false || $changeSuccess === false) {
                $exceptedReturn = [
                    'status' => false,
                    'changePasswordForm' => $form,
                ];
            }
            if ($exceptedReturn) {
                static::assertIsArray($result);
                static::assertArrayHasKey('status', $result);
                static::assertArrayHasKey('changePasswordForm', $result);
                static::assertEquals($exceptedReturn, $result);
            } else {
                static::assertInstanceOf(Response::class, $result);
                static::assertSame($response, $result);
            }
        }
    }


    /**
     * @dataProvider providerTestChangeAction
     * @depend testActionControllHasIdentity
     */
    public function testChangeEmailAction($status, $postRedirectGetReturn, $isValid, $changeSuccess)
    {
        $controller = $this->controller;
        $response = new Response();
        $userService = $this->createMock(UserService::class);
        $authService = $this->createMock(AuthenticationService::class);
        $identity = new UserIdentity();

        $controller->setUserService($userService);

        $this->setUpZfcUserAuthenticationPlugin(
            [
            'hasIdentity'=>true
            ]
        );

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller->setChangeEmailForm($form);

        $userService->expects(static::once())
            ->method('getAuthService')
            ->willReturn($authService);

        $authService->expects(static::once())
            ->method('getIdentity')
            ->willReturn($identity);
        $identity->setEmail('user@example.com');


        $requestParams = $this->createMock(Parameters::class);
        $requestParams->expects(static::once())
            ->method('set')
            ->with('identity', $identity->getEmail());

        $request = $this->createMock(Request::class);
        $request->expects(static::once())
            ->method('getPost')
            ->willReturn($requestParams);
        $this->helperMakePropertyAccessable($controller, 'request', $request);



        $flashMessenger = $this->createMock(
            FlashMessenger::class
        );
        $this->pluginManagerPlugins['flashMessenger']= $flashMessenger;

        $flashMessenger
            ->method('setNamespace')
            ->with('change-email')
            ->will(static::returnSelf());

        $flashMessenger->expects(static::once())
            ->method('getMessages')
            ->willReturn($status ? ['test'] : []);


        $prg = $this->createMock(PostRedirectGet::class);
        $this->pluginManagerPlugins['prg'] = $prg;


        $prg->expects(static::once())
            ->method('__invoke')
            ->with($controller::ROUTE_CHANGEEMAIL)
            ->willReturn($postRedirectGetReturn);

        if ($postRedirectGetReturn !== false && !($postRedirectGetReturn instanceof Response)) {
            $form->expects(static::once())
                ->method('setData')
                ->with($postRedirectGetReturn);

            $form->expects(static::once())
                ->method('isValid')
                ->willReturn((bool)$isValid);

            if ($isValid) {
                $userService->expects(static::once())
                    ->method('changeEmail')
                    ->with($postRedirectGetReturn)
                    ->willReturn((bool)$changeSuccess);


                if ($changeSuccess) {
                    $flashMessenger->expects(static::once())
                        ->method('addMessage')
                        ->with(true);


                    $redirect = $this->createMock(Redirect::class);
                    $redirect->expects(static::once())
                        ->method('toRoute')
                        ->with($controller::ROUTE_CHANGEEMAIL)
                        ->willReturn($response);

                    $this->pluginManagerPlugins['redirect']= $redirect;
                } else {
                    $flashMessenger->expects(static::once())
                        ->method('addMessage')
                        ->with(false);
                }
            }
        }


        $result = $controller->changeEmailAction();
        $exceptedReturn = null;

        if ($postRedirectGetReturn instanceof Response) {
            static::assertInstanceOf(Response::class, $result);
            static::assertSame($postRedirectGetReturn, $result);
        } else {
            if ($postRedirectGetReturn === false) {
                $exceptedReturn = [
                    'status' => $status ? 'test' : null,
                    'changeEmailForm' => $form,
                ];
            } elseif ($isValid === false || $changeSuccess === false) {
                $exceptedReturn = [
                    'status' => false,
                    'changeEmailForm' => $form,
                ];
            }

            if ($exceptedReturn) {
                static::assertIsArray($result);
                static::assertArrayHasKey('status', $result);
                static::assertArrayHasKey('changeEmailForm', $result);
                static::assertEquals($exceptedReturn, $result);
            } else {
                static::assertInstanceOf(Response::class, $result);
                static::assertSame($response, $result);
            }
        }
    }

    /**
     * @dataProvider providerTestSetterGetterServices
     * @depend testActionControllHasIdentity
     */
    public function testSetterGetterServices(
        $method,
        $useServiceLocator,
        $servicePrototype,
        $serviceName,
        $callback = null
    ) {
        $controller = new Controller($this->redirectCallback);
        $controller->setPluginManager($this->pluginManager);

        if (is_callable($callback)) {
            $callback($this, $controller);
        }

        if ($useServiceLocator) {
            $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
            $serviceLocator->expects(static::once())
                ->method('get')
                ->with($serviceName)
                ->willReturn($servicePrototype);
            $controller->setServiceLocator($serviceLocator);
        } else {
            $controller->{'set' . $method}($servicePrototype);
        }

        $result = $controller->{'get' . $method}();
        static::assertInstanceOf(get_class($servicePrototype), $result);
        static::assertSame($servicePrototype, $result);

        // we need two check for every case
        $result = $controller->{'get' . $method}();
        static::assertInstanceOf(get_class($servicePrototype), $result);
        static::assertSame($servicePrototype, $result);
    }

    public function providerTrueOrFalse()
    {
        return [
            [true],
            [false],
        ];
    }

    public function providerTrueOrFalseX2()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    public function providerTestAuthenticateAction()
    {
        // $redirect, $post, $query, $prepareResult = false, $authValid = false
        return [
            [false, null, null, new Response(), false],
            [false, null, null, false, false],
            [false, null, null, false, true],
            [false, 'localhost/test1', null, false, false],
            [false, 'localhost/test1', null, false, true],
            [false, 'localhost/test1', 'localhost/test2', false, false],
            [false, 'localhost/test1', 'localhost/test2', false, true],
            [false, null, 'localhost/test2', false, false],
            [false, null, 'localhost/test2', false, true],

            [true, null, null, false, false],
            [true, null, null, false, true],
            [true, 'localhost/test1', null, false, false],
            [true, 'localhost/test1', null, false, true],
            [true, 'localhost/test1', 'localhost/test2', false, false],
            [true, 'localhost/test1', 'localhost/test2', false, true],
            [true, null, 'localhost/test2', false, false],
            [true, null, 'localhost/test2', false, true],
        ];
    }

    public function providerRedirectPostQueryMatrix()
    {
        return [
            [false, false, false],
            [true, false, false],
            [true, 'localhost/test1', false],
            [true, 'localhost/test1', 'localhost/test2'],
            [true, false, 'localhost/test2'],
        ];
    }

    public function providerTestSetterGetterServices()
    {
        $that = $this;
        $loginFormCallback[] = static function ($that, $controller) {
            $flashMessenger = $that->createMock(
                FlashMessenger::class
            );
            $that->pluginManagerPlugins['flashMessenger']= $flashMessenger;

            $flashMessenger
                ->method('setNamespace')
                ->with('zfcuser-login-form')
                ->will($that->returnSelf());
        };
        $loginFormCallback[] = static function ($that, $controller) {
            $flashMessenger = $that->createMock(
                FlashMessenger::class
            );
            $that->pluginManagerPlugins['flashMessenger']= $flashMessenger;

            $flashMessenger
                ->method('setNamespace')
                ->with('zfcuser-login-form')
                ->will($that->returnSelf());
        };



        return [
            // $method, $useServiceLocator, $servicePrototype, $serviceName, $loginFormCallback
            ['UserService', true, new UserService(), 'zfcuser_user_service'],
            ['UserService', false, new UserService(), null],
            ['RegisterForm', true, new Form(), 'zfcuser_register_form'],
            ['RegisterForm', false, new Form(), null],
            ['ChangePasswordForm', true, new Form(), 'zfcuser_change_password_form'],
            ['ChangePasswordForm', false, new Form(), null],
            ['ChangeEmailForm', true, new Form(), 'zfcuser_change_email_form'],
            ['ChangeEmailForm', false, new Form(), null],
            ['LoginForm', true, new Form(), 'zfcuser_login_form', $loginFormCallback[0]],
            ['LoginForm', true, new Form(), 'zfcuser_login_form', $loginFormCallback[1]],
            ['LoginForm', false, new Form(), null, $loginFormCallback[0]],
            ['LoginForm', false, new Form(), null, $loginFormCallback[1]],
            ['Options', true, new ModuleOptions(), 'zfcuser_module_options'],
            ['Options', false, new ModuleOptions(), null],
        ];
    }


    public function providerTestActionControllHasIdentity()
    {

        return [
            //    $methodeName , $hasIdentity, $redirectRoute,           optionsGetterMethode
            ['indexAction', false, Controller::ROUTE_LOGIN, null],
            ['loginAction', true, 'user/overview', 'getLoginRedirectRoute'],
            ['authenticateAction', true, 'user/overview', 'getLoginRedirectRoute'],
            ['registerAction', true, 'user/overview', 'getLoginRedirectRoute'],
            ['changepasswordAction', false, 'user/overview', 'getLoginRedirectRoute'],
            ['changeEmailAction', false, 'user/overview', 'getLoginRedirectRoute']

        ];
    }


    public function providerTestChangeAction()
    {
        return [
            //    $status, $postRedirectGetReturn, $isValid, $changeSuccess
            [false, new Response(), null, null],
            [true, new Response(), null, null],

            [false, false, null, null],
            [true, false, null, null],

            [false, ['test'], false, null],
            [true, ['test'], false, null],

            [false, ['test'], true, false],
            [true, ['test'], true, false],

            [false, ['test'], true, true],
            [true, ['test'], true, true],

        ];
    }


    public function providerTestRegisterAction()
    {
        $registerPost = [
            'username'=>'zfc-user',
            'email'=>'zfc-user@trash-mail.com',
            'password'=>'secret'
        ];
        $registerPostRedirect = array_merge($registerPost, ['redirect' => 'test']);


        return [
            //    $status, $postRedirectGetReturn, $registerSuccess, $loginAfterSuccessWith
            [false, new Response(), null, null],
            [true, new Response(), null, null],

            [false, false, null, null],
            [true, false, null, null],

            [false, $registerPost, false, null],
            [true, $registerPost, false, null],
            [false, $registerPostRedirect, false, null],
            [true, $registerPostRedirect, false, null],

            [false, $registerPost, true, 'email'],
            [true, $registerPost, true, 'email'],
            [false, $registerPostRedirect, true, 'email'],
            [true, $registerPostRedirect, true, 'email'],

            [false, $registerPost, true, 'username'],
            [true, $registerPost, true, 'username'],
            [false, $registerPostRedirect, true, 'username'],
            [true, $registerPostRedirect, true, 'username'],

            [false, $registerPost, true, null],
            [true, $registerPost, true, null],
            [false, $registerPostRedirect, true, null],
            [true, $registerPostRedirect, true, null],

        ];
    }


    /**
     *
     * @param mixed $objectOrClass
     * @param string $property
     * @param mixed $value = null
     * @return ReflectionProperty
     */
    public function helperMakePropertyAccessable($objectOrClass, $property, $value = null)
    {
        $reflectionProperty = new ReflectionProperty($objectOrClass, $property);
        $reflectionProperty->setAccessible(true);

        if ($value !== null) {
            $reflectionProperty->setValue($objectOrClass, $value);
        }
        return $reflectionProperty;
    }

    public function helperMockCallbackPluginManagerGet($key)
    {
        if ($key === 'flashMessenger' && !array_key_exists($key, $this->pluginManagerPlugins)) {
//             echo "\n\n";
//             echo '$key: ' . $key . "\n";
//             var_dump(array_key_exists($key, $this->pluginManagerPlugins), array_keys($this->pluginManagerPlugins));
//             exit;
        }
        return $this->pluginManagerPlugins[$key] ?? null;
    }
}
