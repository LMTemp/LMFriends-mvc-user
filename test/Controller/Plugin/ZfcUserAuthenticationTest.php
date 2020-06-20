<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Controller\Plugin;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication as Plugin;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Adapter\AdapterInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain;

class ZfcUserAuthenticationTest extends TestCase
{
    /**
     *
     * @var Plugin
     */
    protected $SUT;

    /**
     *
     * @var AuthenticationService
     */
    protected $mockedAuthenticationService;

    /**
     *
     * @var AdapterChain
     */
    protected $mockedAuthenticationAdapter;

    protected function setUp(): void
    {
        $this->SUT = new Plugin();
        $this->mockedAuthenticationService = $this->createMock(AuthenticationService::class);
        $this->mockedAuthenticationAdapter = $this->getMockForAbstractClass(AdapterChain::class);
    }


    /**
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication::hasIdentity
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication::getIdentity
     */
    public function testGetAndHasIdentity()
    {
        $this->SUT->setAuthService($this->mockedAuthenticationService);

        $callbackIndex = 0;
        $callback = static function () use (&$callbackIndex) {
            $callbackIndex++;
            return (bool) ($callbackIndex % 2);
        };

        $this->mockedAuthenticationService
                                          ->method('hasIdentity')
                                          ->willReturnCallback($callback);

        $this->mockedAuthenticationService
                                          ->method('getIdentity')
                                          ->willReturnCallback($callback);

        static::assertTrue($this->SUT->hasIdentity());
        static::assertFalse($this->SUT->hasIdentity());
        static::assertTrue($this->SUT->hasIdentity());

        $callbackIndex= 0;

        static::assertTrue($this->SUT->getIdentity());
        static::assertFalse($this->SUT->getIdentity());
        static::assertTrue($this->SUT->getIdentity());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication::setAuthAdapter
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication::getAuthAdapter
     */
    public function testSetAndGetAuthAdapter()
    {
        $adapter1 = $this->mockedAuthenticationAdapter;
        $adapter2 = new AdapterChain();
        $this->SUT->setAuthAdapter($adapter1);

        static::assertInstanceOf(AdapterInterface::class, $this->SUT->getAuthAdapter());
        static::assertSame($adapter1, $this->SUT->getAuthAdapter());

        $this->SUT->setAuthAdapter($adapter2);

        static::assertInstanceOf(AdapterInterface::class, $this->SUT->getAuthAdapter());
        static::assertNotSame($adapter1, $this->SUT->getAuthAdapter());
        static::assertSame($adapter2, $this->SUT->getAuthAdapter());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication::setAuthService
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthentication::getAuthService
     */
    public function testSetAndGetAuthService()
    {
        $service1 = new AuthenticationService();
        $service2 = new AuthenticationService();
        $this->SUT->setAuthService($service1);

        static::assertInstanceOf(AuthenticationService::class, $this->SUT->getAuthService());
        static::assertSame($service1, $this->SUT->getAuthService());

        $this->SUT->setAuthService($service2);

        static::assertInstanceOf(AuthenticationService::class, $this->SUT->getAuthService());
        static::assertNotSame($service1, $this->SUT->getAuthService());
        static::assertSame($service2, $this->SUT->getAuthService());
    }
}
