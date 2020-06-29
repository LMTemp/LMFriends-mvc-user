<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Form\FormInterface;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Forward;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Plugin\Prg\PostRedirectGet;
use Laminas\View\Model\ViewModel;
use LaminasFriends\Mvc\User\Entity\UserEntity;
use LaminasFriends\Mvc\User\Form\ChangeEmailForm;
use LaminasFriends\Mvc\User\Form\ChangePasswordForm;
use LaminasFriends\Mvc\User\Form\RegisterForm;
use LaminasFriends\Mvc\User\Module;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;
use LaminasFriends\Mvc\User\Controller\RedirectCallback;
use LaminasFriends\Mvc\User\Controller\UserController;
use Laminas\Http\Response;
use Laminas\Stdlib\Parameters;
use LaminasFriends\Mvc\User\Service\UserService;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainService;
use LaminasFriends\Mvc\User\Form\LoginForm;
use LaminasFriends\Mvc\User\Controller\Plugin\UserAuthenticationPlugin;

class UserControllerTest extends TestCase
{
    protected array $pluginManagerPlugins = [];
    protected UserController $controller;
    protected $pluginManager;
    protected $mvcUserAuthenticationPlugin;

    protected ModuleOptions $options;

    protected UserService $userService;
    protected FormInterface $loginForm;
    protected FormInterface $registerForm;
    protected FormInterface $changePasswordForm;
    protected FormInterface $changeEmailForm;

    /**
     * @var MockObject|RedirectCallback
     */
    protected $redirectCallback;

    /**
     * @dataProvider providerTestActionControllerHasIdentity
     */
    public function testActionControllerHasIdentity($methodeName, $hasIdentity, $redirectRoute, $optionGetter)
    {
        $redirectRoute = $redirectRoute ?: Module::ROUTE_LOGIN;

        $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => $hasIdentity,
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

        $this->pluginManagerPlugins['redirect'] = $redirect;

        $result = $this->controller->$methodeName();

        static::assertInstanceOf(Response::class, $result);
        static::assertSame($response, $result);
    }

    public function setUpMvcUserAuthenticationPlugin($option)
    {
        if (array_key_exists('hasIdentity', $option)) {
            $return = (is_callable($option['hasIdentity']))
                ? static::returnCallback($option['hasIdentity'])
                : static::returnValue($option['hasIdentity']);
            $this->mvcUserAuthenticationPlugin
                ->method('hasIdentity')
                ->will($return);
        }

        if (array_key_exists('getAuthAdapter', $option)) {
            $return = (is_callable($option['getAuthAdapter']))
                ? static::returnCallback($option['getAuthAdapter'])
                : static::returnValue($option['getAuthAdapter']);

            $this->mvcUserAuthenticationPlugin
                ->method('getAuthAdapter')
                ->will($return);
        }

        if (array_key_exists('getAuthService', $option)) {
            $return = (is_callable($option['getAuthService']))
                ? static::returnCallback($option['getAuthService'])
                : static::returnValue($option['getAuthService']);

            $this->mvcUserAuthenticationPlugin
                ->method('getAuthService')
                ->will($return);
        }

        $this->pluginManagerPlugins['mvcUserAuthentication'] = $this->mvcUserAuthenticationPlugin;

        return $this->mvcUserAuthenticationPlugin;
    }

    /**
     * @depends testActionControllerHasIdentity
     */
    public function testIndexActionLoggedIn()
    {
        $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => true,
            ]
        );

        $result = $this->controller->indexAction();

        static::assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @dataProvider providerTrueOrFalseX2
     * @depends      testActionControllerHasIdentity
     */
    public function testLoginActionValidFormRedirectFalse($isValid, $wantRedirect)
    {
        $redirectUrl = 'localhost/redirect1';

        $plugin = $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => false,
            ]
        );

        $flashMessenger = $this->createMock(
            FlashMessenger::class
        );
        $this->pluginManagerPlugins['flashMessenger'] = $flashMessenger;

        $flashMessenger
            ->method('setNamespace')
            ->with('mvcuser-login-form')
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

        $this->helperMakePropertyAccessable($this->controller, 'request', $request);

        $this->loginForm->method('isValid')
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
            $adapter = $this->createMock(AdapterChainService::class);
            $adapter->expects(static::once())
                ->method('resetAdapters');

            $service = $this->createMock(AuthenticationService::class);
            $service->expects(static::once())
                ->method('clearIdentity');

            $plugin = $this->setUpMvcUserAuthenticationPlugin(
                [
                    'getAuthAdapter' => $adapter,
                    'getAuthService' => $service,
                ]
            );

            $this->loginForm->expects(static::once())
                ->method('setData')
                ->with($postArray);

            $expectedResult = new stdClass();

            $forwardPlugin = $this->getMockBuilder(Forward::class)
                ->disableOriginalConstructor()
                ->getMock();
            $forwardPlugin->expects(static::once())
                ->method('dispatch')
                ->with(Module::CONTROLLER_NAME, ['action' => 'authenticate'])
                ->willReturn($expectedResult);

            $this->pluginManagerPlugins['forward'] = $forwardPlugin;
        } else {
            $response = new Response();

            $redirectQuery = $wantRedirect ? '?redirect=' . rawurlencode($redirectUrl) : '';
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

            $this->pluginManagerPlugins['redirect'] = $redirect;


            $response = new Response();
            $url = $this->createMock(Url::class, ['fromRoute']);
            $url->expects(static::once())
                ->method('fromRoute')
                ->with(Module::ROUTE_LOGIN)
                ->willReturn($route_url);

            $this->pluginManagerPlugins['url'] = $url;
            $TEST = true;
        }


        $result = $this->controller->loginAction();

        if ($isValid) {
            static::assertSame($expectedResult, $result);
        } else {
            static::assertInstanceOf(Response::class, $result);
            static::assertEquals($response, $result);
            static::assertEquals($route_url . $redirectQuery, $result->getHeaders()->get('Location')->getFieldValue());
        }
    }

    /**
     *
     * @param mixed  $objectOrClass
     * @param string $property
     * @param mixed  $value = null
     *
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

    /**
     * @dataProvider providerTrueOrFalse
     * @depends      testActionControllerHasIdentity
     */
    public function testLoginActionIsNotPost($redirect)
    {
        $plugin = $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => false,
            ]
        );

        $flashMessenger = $this->createMock(FlashMessenger::class);

        $this->pluginManagerPlugins['flashMessenger'] = $flashMessenger;

        $request = $this->createMock(Request::class);
        $request->expects(static::once())
            ->method('isPost')
            ->willReturn(false);

        $this->loginForm->expects(static::never())
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

        $result = $this->controller->loginAction();

        static::assertArrayHasKey('loginForm', $result);
        static::assertArrayHasKey('redirect', $result);
        static::assertArrayHasKey('enableRegistration', $result);

        static::assertInstanceOf(LoginForm::class, $result['loginForm']);
        static::assertSame($this->loginForm, $result['loginForm']);

        if ($redirect) {
            static::assertEquals('http://localhost/', $result['redirect']);
        } else {
            static::assertFalse($result['redirect']);
        }

        static::assertEquals($this->options->getEnableRegistration(), $result['enableRegistration']);
    }

    /**
     * @dataProvider providerRedirectPostQueryMatrix
     * @depends      testActionControllerHasIdentity
     */
    public function testLogoutAction($withRedirect, $post, $query)
    {
        $adapter = $this->createMock(AdapterChainService::class);
        $adapter->expects(static::once())
            ->method('resetAdapters');

        $adapter->expects(static::once())
            ->method('logoutAdapters');

        $service = $this->createMock(AuthenticationService::class);
        $service->expects(static::once())
            ->method('clearIdentity');

        $this->setUpMvcUserAuthenticationPlugin(
            [
                'getAuthAdapter' => $adapter,
                'getAuthService' => $service,
            ]
        );

        $response = new Response();

        $this->redirectCallback->expects(static::once())
            ->method('__invoke')
            ->willReturn($response);

        $result = $this->controller->logoutAction();

        static::assertInstanceOf(Response::class, $result);
        static::assertSame($response, $result);
    }

    public function testLoginRedirectFailsWithUrl()
    {
    }

    /**
     * @dataProvider providerTestAuthenticateAction
     * @depends      testActionControllerHasIdentity
     */
    public function testAuthenticateAction($wantRedirect, $post, $query, $prepareResult = false, $authValid = false)
    {
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
        $this->helperMakePropertyAccessable($this->controller, 'request', $request);

        $adapter = $this->createMock(AdapterChainService::class);
        $adapter->expects(static::once())
            ->method('prepareForAuthentication')
            ->with($request)
            ->willReturn($prepareResult);

        $service = $this->createMock(AuthenticationService::class);


        $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity'    => false,
                'getAuthAdapter' => $adapter,
                'getAuthService' => $service,
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
                $this->pluginManagerPlugins['flashMessenger'] = $flashMessenger;

                $flashMessenger->expects(static::once())
                    ->method('setNamespace')
                    ->with('mvcuser-login-form')
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
                    ->with(Module::ROUTE_LOGIN)
                    ->willReturn('user/login');
                $this->pluginManagerPlugins['url'] = $url;
            }

            $this->options
                ->method('getUseRedirectParameterIfPresent')
                ->willReturn((bool)$wantRedirect);
        }
        $this->controller->authenticateAction();
    }

    /**
     *
     * @depends testActionControllerHasIdentity
     */
    public function testRegisterActionIsNotAllowed()
    {
        $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => false,
            ]
        );

        $this->options->expects(static::once())
            ->method('getEnableRegistration')
            ->willReturn(false);

        $result = $this->controller->registerAction();

        static::assertIsArray($result);
        static::assertArrayHasKey('enableRegistration', $result);
        static::assertFalse($result['enableRegistration']);
    }

    /**
     *
     * @dataProvider providerTestRegisterAction
     * @depends      testActionControllerHasIdentity
     * @depends      testRegisterActionIsNotAllowed
     */
    public function testRegisterAction($wantRedirect, $postRedirectGetReturn, $registerSuccess, $loginAfterSuccessWith)
    {
        $redirectUrl = 'localhost/redirect1';
        $route_url = '/user/register';
        $expectedResult = null;

        $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => false,
            ]
        );

        $this->options
            ->method('getEnableRegistration')
            ->willReturn(true);

        $this->options->method('getUserEntityClass')
            ->willReturn(UserEntity::class);

        $request = $this->createMock(Request::class);
        $this->helperMakePropertyAccessable($this->controller, 'request', $request);

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
            ->with(Module::ROUTE_REGISTER)
            ->willReturn($route_url);

        $this->pluginManagerPlugins['url'] = $url;

        $prg = $this->createMock(PostRedirectGet::class);
        $this->pluginManagerPlugins['prg'] = $prg;

        $redirectQuery = $wantRedirect ? '?redirect=' . rawurlencode($redirectUrl) : '';
        $prg->expects(static::once())
            ->method('__invoke')
            ->with($route_url . $redirectQuery)
            ->willReturn($postRedirectGetReturn);

        if ($registerSuccess) {
            $user = new UserEntity();
            $user->setEmail('mvc-user@trash-mail.com');
            $user->setUsername('mvc-user');

            $this->registerForm->method('isValid')
                ->willReturn(true);
            $this->registerForm->method('getData')
                ->willReturn($user);

            $this->userService->expects(static::once())
                ->method('register')
                ->with($user)
                ->willReturn($user);

            $this->userService->method('getOptions')
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
                    ->with(Module::CONTROLLER_NAME, ['action' => 'authenticate'])
                    ->willReturn($expectedResult);

                $this->pluginManagerPlugins['forward'] = $forwardPlugin;
            } else {
                $response = new Response();
                $route_url = '/user/login';

                $redirectUrl = $postRedirectGetReturn['redirect'] ?? null;

                $redirectQuery = $redirectUrl ? '?redirect=' . rawurlencode($redirectUrl) : '';

                $redirect = $this->createMock(Redirect::class);
                $redirect->expects(static::once())
                    ->method('toUrl')
                    ->with($route_url . $redirectQuery)
                    ->willReturn($response);

                $this->pluginManagerPlugins['redirect'] = $redirect;

                $url->expects(static::at(1))
                    ->method('fromRoute')
                    ->with(Module::ROUTE_LOGIN)
                    ->willReturn($route_url);
            }
        }

        /***********************************************
         * run
         */
        $result = $this->controller->registerAction();

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
                'registerForm'       => $this->registerForm,
                'enableRegistration' => true,
                'redirect'           => $wantRedirect ? $redirectUrl : false,
            ];
        } elseif ($registerSuccess === false) {
            $expectedResult = [
                'registerForm'       => $this->registerForm,
                'enableRegistration' => true,
                'redirect'           => $postRedirectGetReturn['redirect'] ?? null,
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
     * @depends      testActionControllerHasIdentity
     */
    public function testChangepasswordAction($status, $postRedirectGetReturn, $isValid, $changeSuccess)
    {
        $response = new Response();

        $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => true,
            ]
        );

        $flashMessenger = $this->createMock(
            FlashMessenger::class
        );
        $this->pluginManagerPlugins['flashMessenger'] = $flashMessenger;

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
            ->with(Module::ROUTE_CHANGEPASSWD)
            ->willReturn($postRedirectGetReturn);

        if ($postRedirectGetReturn !== false && !($postRedirectGetReturn instanceof Response)) {
            $this->changePasswordForm->expects(static::once())
                ->method('setData')
                ->with($postRedirectGetReturn);

            $this->changePasswordForm->expects(static::once())
                ->method('isValid')
                ->willReturn((bool)$isValid);

            if ($isValid) {
                $this->changePasswordForm->expects(static::once())
                    ->method('getData')
                    ->willReturn($postRedirectGetReturn);

                $this->userService->expects(static::once())
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
                        ->with(Module::ROUTE_CHANGEPASSWD)
                        ->willReturn($response);

                    $this->pluginManagerPlugins['redirect'] = $redirect;
                }
            }
        }

        $result = $this->controller->changepasswordAction();
        $exceptedReturn = null;

        if ($postRedirectGetReturn instanceof Response) {
            static::assertInstanceOf(Response::class, $result);
            static::assertSame($postRedirectGetReturn, $result);
        } else {
            if ($postRedirectGetReturn === false) {
                $exceptedReturn = [
                    'status'             => $status ? 'test' : null,
                    'changePasswordForm' => $this->changePasswordForm,
                ];
            } elseif ($isValid === false || $changeSuccess === false) {
                $exceptedReturn = [
                    'status'             => false,
                    'changePasswordForm' => $this->changePasswordForm,
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
     * @depends      testActionControllerHasIdentity
     */
    public function testChangeEmailAction($status, $postRedirectGetReturn, $isValid, $changeSuccess)
    {
        $response = new Response();
        $authService = $this->createMock(AuthenticationService::class);
        $identity = new UserEntity();

        $this->setUpMvcUserAuthenticationPlugin(
            [
                'hasIdentity' => true,
            ]
        );

        $this->userService->expects(static::once())
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
        $this->helperMakePropertyAccessable($this->controller, 'request', $request);

        $flashMessenger = $this->createMock(
            FlashMessenger::class
        );
        $this->pluginManagerPlugins['flashMessenger'] = $flashMessenger;

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
            ->with(Module::ROUTE_CHANGEEMAIL)
            ->willReturn($postRedirectGetReturn);

        if ($postRedirectGetReturn !== false && !($postRedirectGetReturn instanceof Response)) {
            $this->changeEmailForm->expects(static::once())
                ->method('setData')
                ->with($postRedirectGetReturn);

            $this->changeEmailForm->expects(static::once())
                ->method('isValid')
                ->willReturn((bool)$isValid);

            if ($isValid) {
                $this->userService->expects(static::once())
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
                        ->with(Module::ROUTE_CHANGEEMAIL)
                        ->willReturn($response);

                    $this->pluginManagerPlugins['redirect'] = $redirect;
                } else {
                    $flashMessenger->expects(static::once())
                        ->method('addMessage')
                        ->with(false);
                }
            }
        }

        $result = $this->controller->changeEmailAction();
        $exceptedReturn = null;

        if ($postRedirectGetReturn instanceof Response) {
            static::assertInstanceOf(Response::class, $result);
            static::assertSame($postRedirectGetReturn, $result);
        } else {
            if ($postRedirectGetReturn === false) {
                $exceptedReturn = [
                    'status'          => $status ? 'test' : null,
                    'changeEmailForm' => $this->changeEmailForm,
                ];
            } elseif ($isValid === false || $changeSuccess === false) {
                $exceptedReturn = [
                    'status'          => false,
                    'changeEmailForm' => $this->changeEmailForm,
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

    public function providerTestActionControllerHasIdentity()
    {
        return [
            //    $methodeName , $hasIdentity, $redirectRoute,           optionsGetterMethode
            ['indexAction', false, Module::ROUTE_LOGIN, null],
            ['loginAction', true, 'user/overview', 'getLoginRedirectRoute'],
            ['authenticateAction', true, 'user/overview', 'getLoginRedirectRoute'],
            ['registerAction', true, 'user/overview', 'getLoginRedirectRoute'],
            ['changepasswordAction', false, 'user/overview', 'getLoginRedirectRoute'],
            ['changeEmailAction', false, 'user/overview', 'getLoginRedirectRoute'],

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
            'username' => 'mvc-user',
            'email'    => 'mvc-user@trash-mail.com',
            'password' => 'secret',
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

    protected function setUp(): void
    {
        $this->redirectCallback = $this->getMockBuilder(RedirectCallback::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userService = $this->createMock(UserService::class);
        $this->options = $this->createMock(ModuleOptions::class);
        $this->loginForm = $this->getMockBuilder(LoginForm::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registerForm = $this->getMockBuilder(RegisterForm::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->changeEmailForm = $this->getMockBuilder(ChangeEmailForm::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->changePasswordForm = $this->getMockBuilder(ChangePasswordForm::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mvcUserAuthenticationPlugin = $this->createMock(UserAuthenticationPlugin::class);

        $this->pluginManager = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pluginManager->method('get')
            ->willReturnCallback([$this, 'helperMockCallbackPluginManagerGet']);

        $this->controller = new UserController(
            $this->options,
            $this->userService,
            $this->redirectCallback,
            $this->loginForm,
            $this->registerForm,
            $this->changePasswordForm,
            $this->changeEmailForm
        );

        $this->controller->setPluginManager($this->pluginManager);
    }
}
