<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Controller;

use Laminas\Http\Headers;
use LaminasFriends\Mvc\User\Module;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use LaminasFriends\Mvc\User\Controller\RedirectCallback;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

class RedirectCallbackTest extends TestCase
{

    /** @var RedirectCallback */
    protected $redirectCallback;

    /** @var MockObject|ModuleOptions */
    protected $moduleOptions;

    /** @var  MockObject|RouteInterface */
    protected $router;

    /** @var  MockObject|Application */
    protected $application;

    /** @var  MockObject|Request */
    protected $request;

    /** @var  MockObject|Response */
    protected $response;

    /** @var  MockObject|MvcEvent */
    protected $mvcEvent;

    /** @var  MockObject|RouteMatch */
    protected $routeMatch;

    protected function setUp(): void
    {
        $this->router = $this->getMockBuilder(RouteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleOptions = $this->getMockBuilder(ModuleOptions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setUpApplication();

        $this->redirectCallback = new RedirectCallback(
            $this->application,
            $this->router,
            $this->moduleOptions
        );
    }

    public function testInvoke(): void
    {
        $url = 'someUrl';

        $this->routeMatch->expects(static::once())
            ->method('getMatchedRouteName')
            ->willReturn('someRoute');

        $headers = $this->createMock(Headers::class);
        $headers->expects(static::once())
            ->method('addHeaderLine')
            ->with('Location', $url);

        $this->router
            ->method('assemble')
            ->with([], ['name' => 'mvcuser'])
            ->willReturn($url);

        $this->response->expects(static::once())
            ->method('getHeaders')
            ->willReturn($headers);

        $this->response->expects(static::once())
            ->method('setStatusCode')
            ->with(302);

        $result = $this->redirectCallback->__invoke();

        static::assertSame($this->response, $result);
    }

    /**
     * @dataProvider providerGetRedirectRouteFromRequest
     */
    public function testGetRedirectRouteFromRequest($get, $post, $getRouteExists, $postRouteExists)
    {
        $expectedResult = false;

        $this->request->expects(static::once())
            ->method('getQuery')
            ->willReturn($get);

        if ($get) {
            $this->router
                ->method('assemble')
                ->with([], ['name' => $get])
                ->will($getRouteExists);

            if ($getRouteExists == static::returnValue(true)) {
                $expectedResult = $get;
            }
        }

        if (!$get || !$getRouteExists) {
            $this->request->expects(static::once())
                ->method('getPost')
                ->willReturn($post);

            if ($post) {
                $this->router
                    ->method('assemble')
                    ->with([], ['name' => $post])
                    ->will($postRouteExists);

                if ($postRouteExists == static::returnValue(true)) {
                    $expectedResult = $post;
                }
            }
        }

        $method = new ReflectionMethod(
            RedirectCallback::class,
            'getRedirectRouteFromRequest'
        );
        $method->setAccessible(true);
        $result = $method->invoke($this->redirectCallback);

        static::assertSame($expectedResult, $result);
    }

    public function providerGetRedirectRouteFromRequest(): array
    {
        return [
            ['user', false, static::returnValue('route'), false],
            ['user', false, static::returnValue('route'), static::returnValue(true)],
            ['user', 'user', static::returnValue('route'), static::returnValue(true)],
            ['user', 'user', static::throwException(new RuntimeException()), static::returnValue(true)],
            ['user', 'user', static::throwException(new RuntimeException()), static::throwException(
                new RuntimeException()
            )],
            [false, 'user', false, static::returnValue(true)],
            [false, 'user', false, static::throwException(new RuntimeException())],
            [false, 'user', false, static::throwException(new RuntimeException())],
        ];
    }

    public function testRouteExistsRouteExists()
    {
        $route = 'existingRoute';

        $this->router->expects(static::once())
            ->method('assemble')
            ->with([], ['name' => $route]);

        $method = new ReflectionMethod(
            RedirectCallback::class,
            'routeExists'
        );
        $method->setAccessible(true);
        $result = $method->invoke($this->redirectCallback, $route);

        static::assertTrue($result);
    }

    public function testRouteExistsRouteDoesntExists()
    {
        $route = 'existingRoute';

        $this->router->expects(static::once())
            ->method('assemble')
            ->with([], ['name' => $route])
            ->will(static::throwException(new RuntimeException()));

        $method = new ReflectionMethod(
            RedirectCallback::class,
            'routeExists'
        );
        $method->setAccessible(true);
        $result = $method->invoke($this->redirectCallback, $route);

        static::assertFalse($result);
    }

    /**
     * @dataProvider providerGetRedirectNoRedirectParam
     */
    public function testGetRedirectNoRedirectParam($currentRoute, $optionsReturn, $expectedResult, $optionsMethod)
    {
        $this->moduleOptions->expects(static::once())
            ->method('getUseRedirectParameterIfPresent')
            ->willReturn(true);

        $this->router->expects(static::at(0))
            ->method('assemble');
        $this->router->expects(static::at(1))
            ->method('assemble')
            ->with([], ['name' => $optionsReturn])
            ->willReturn($expectedResult);

        if ($optionsMethod) {
            $this->moduleOptions->expects(static::never())
                ->method($optionsMethod)
                ->willReturn($optionsReturn);
        }
        $method = new ReflectionMethod(
            RedirectCallback::class,
            'getRedirect'
        );
        $method->setAccessible(true);
        $result = $method->invoke($this->redirectCallback, $currentRoute, $optionsReturn);

        static::assertSame($expectedResult, $result);
    }

    public function providerGetRedirectNoRedirectParam()
    {
        return [
            [Module::ROUTE_LOGIN, Module::ROUTE_BASE, '/user', 'getLoginRedirectRoute'],
            [Module::ROUTE_AUTHENTICATE, Module::CONTROLLER_NAME, '/user', 'getLoginRedirectRoute'],
            [Module::ROUTE_LOGOUT, Module::ROUTE_LOGIN, '/user/login', 'getLogoutRedirectRoute'],
            ['testDefault', Module::ROUTE_BASE, '/home', false],
        ];
    }

    public function testGetRedirectWithOptionOnButNoRedirect()
    {
        $route = Module::ROUTE_LOGIN;
        $redirect = false;
        $expectedResult = '/user/login';

        $this->moduleOptions->expects(static::once())
            ->method('getUseRedirectParameterIfPresent')
            ->willReturn(true);

        $this->moduleOptions->expects(static::once())
            ->method('getLoginRedirectRoute')
            ->willReturn($route);

        $this->router->expects(static::once())
            ->method('assemble')
            ->with([], ['name' => $route])
            ->willReturn($expectedResult);

        $method = new ReflectionMethod(
            RedirectCallback::class,
            'getRedirect'
        );
        $method->setAccessible(true);
        $result = $method->invoke($this->redirectCallback, $route, $redirect);

        static::assertSame($expectedResult, $result);
    }

    public function testGetRedirectWithOptionOnRedirectDoesntExists()
    {
        $route = Module::ROUTE_LOGIN;
        $redirect = 'doesntExists';
        $expectedResult = '/user/login';

        $this->moduleOptions->expects(static::once())
            ->method('getUseRedirectParameterIfPresent')
            ->willReturn(true);

        $this->router->expects(static::at(0))
            ->method('assemble')
            ->with([], ['name' => $redirect])
            ->will(static::throwException(new RuntimeException()));

        $this->router->expects(static::at(1))
            ->method('assemble')
            ->with([], ['name' => $route])
            ->willReturn($expectedResult);

        $this->moduleOptions->expects(static::once())
            ->method('getLoginRedirectRoute')
            ->willReturn($route);

        $method = new ReflectionMethod(
            RedirectCallback::class,
            'getRedirect'
        );
        $method->setAccessible(true);
        $result = $method->invoke($this->redirectCallback, $route, $redirect);

        static::assertSame($expectedResult, $result);
    }

    private function setUpApplication()
    {
        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeMatch = $this->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mvcEvent = $this->getMockBuilder(MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mvcEvent
            ->method('getRouteMatch')
            ->willReturn($this->routeMatch);


        $this->application
            ->method('getMvcEvent')
            ->willReturn($this->mvcEvent);
        $this->application
            ->method('getRequest')
            ->willReturn($this->request);
        $this->application
            ->method('getResponse')
            ->willReturn($this->response);
    }
}
