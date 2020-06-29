<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Controller\Plugin;

use LaminasFriends\Mvc\User\Controller\Plugin\UserAuthenticationPlugin;
use PHPUnit\Framework\TestCase;
use Laminas\Authentication\AuthenticationService;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainService;

/**
 * Class UserAuthenticationPluginTest
 */
class UserAuthenticationPluginTest extends TestCase
{
    /**
     *
     * @var UserAuthenticationPlugin
     */
    protected $SUT;

    /**
     *
     * @var AuthenticationService
     */
    protected $mockedAuthenticationService;

    /**
     *
     * @var AdapterChainService
     */
    protected $mockedAuthenticationAdapter;

    /**
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\UserAuthenticationPlugin::hasIdentity
     * @covers \LaminasFriends\Mvc\User\Controller\Plugin\UserAuthenticationPlugin::getIdentity
     */
    public function testGetAndHasIdentity()
    {
        $callbackIndex = 0;
        $callback = static function () use (&$callbackIndex) {
            $callbackIndex++;
            return (bool)($callbackIndex % 2);
        };

        $this->mockedAuthenticationService->method('hasIdentity')
            ->willReturnCallback($callback);

        $this->mockedAuthenticationService->method('getIdentity')
            ->willReturnCallback($callback);

        static::assertTrue($this->SUT->hasIdentity());
        static::assertFalse($this->SUT->hasIdentity());
        static::assertTrue($this->SUT->hasIdentity());

        $callbackIndex = 0;

        static::assertTrue($this->SUT->getIdentity());
        static::assertFalse($this->SUT->getIdentity());
        static::assertTrue($this->SUT->getIdentity());
    }

    protected function setUp(): void
    {
        $this->mockedAuthenticationService = $this->createMock(AuthenticationService::class);
        $this->mockedAuthenticationAdapter = $this->createMock(AdapterChainService::class);
        $this->SUT = new UserAuthenticationPlugin(
            $this->mockedAuthenticationAdapter,
            $this->mockedAuthenticationService
        );
    }
}
