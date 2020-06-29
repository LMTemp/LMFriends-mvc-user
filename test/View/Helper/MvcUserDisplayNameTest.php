<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\View\Helper;

use Laminas\Authentication\AuthenticationService;
use LaminasFriends\Mvc\User\Exception\DomainException;
use PHPUnit\Framework\TestCase;
use stdClass;
use LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayName;
use LaminasFriends\Mvc\User\Entity\UserEntity;

class MvcUserDisplayNameTest extends TestCase
{
    protected $helper;

    protected $authService;

    protected $user;

    protected function setUp(): void
    {
        $this->authService = $this->createMock(AuthenticationService::class);
        $this->user = $this->createMock(UserEntity::class);
        $this->helper = new MvcUserDisplayName($this->authService);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserAndNotLoggedIn()
    {
        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(false);

        static::assertNull($this->helper->__invoke(null));
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayName::__invoke
     *
     */
    public function testInvokeWithoutUserButLoggedInWithWrongUserObject()
    {
        $this->expectException(DomainException::class);
        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn(new stdClass());

        $this->helper->__invoke(null);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithDisplayName()
    {
        $this->user->expects(static::once())
                   ->method('getDisplayName')
                   ->willReturn('mvcUser');

        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn($this->user);

        $result = $this->helper->__invoke(null);

        static::assertEquals('mvcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithoutDisplayNameButWithUsername()
    {
        $this->user->expects(static::once())
                   ->method('getDisplayName')
                   ->willReturn(null);
        $this->user->expects(static::once())
                   ->method('getUsername')
                   ->willReturn('mvcUser');

        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn($this->user);

        $result = $this->helper->__invoke(null);

        static::assertEquals('mvcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithoutDisplayNameAndWithOutUsernameButWithEmail()
    {
        $this->user->expects(static::once())
                   ->method('getDisplayName')
                   ->willReturn(null);
        $this->user->expects(static::once())
                   ->method('getUsername')
                   ->willReturn(null);
        $this->user->expects(static::once())
                   ->method('getEmail')
                   ->willReturn('mvcUser@mvcUser.com');

        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn($this->user);

        $result = $this->helper->__invoke(null);

        static::assertEquals('mvcUser', $result);
    }
}
