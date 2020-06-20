<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\View\Helper;

use Laminas\Authentication\AuthenticationService;
use LaminasFriends\Mvc\User\Exception\DomainException;
use PHPUnit\Framework\TestCase;
use stdClass;
use LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName as ViewHelper;
use LaminasFriends\Mvc\User\Entity\UserEntity;

class ZfcUserDisplayNameTest extends TestCase
{
    protected $helper;

    protected $authService;

    protected $user;

    protected function setUp(): void
    {
        $helper = new ViewHelper();
        $this->helper = $helper;

        $authService = $this->createMock(AuthenticationService::class);
        $this->authService = $authService;

        $user = $this->createMock(UserEntity::class);
        $this->user = $user;

        $helper->setAuthService($authService);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserAndNotLoggedIn()
    {
        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(false);

        $result = $this->helper->__invoke(null);

        static::assertFalse($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName::__invoke
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
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithDisplayName()
    {
        $this->user->expects(static::once())
                   ->method('getDisplayName')
                   ->willReturn('zfcUser');

        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn($this->user);

        $result = $this->helper->__invoke(null);

        static::assertEquals('zfcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithoutDisplayNameButWithUsername()
    {
        $this->user->expects(static::once())
                   ->method('getDisplayName')
                   ->willReturn(null);
        $this->user->expects(static::once())
                   ->method('getUsername')
                   ->willReturn('zfcUser');

        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn($this->user);

        $result = $this->helper->__invoke(null);

        static::assertEquals('zfcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName::__invoke
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
                   ->willReturn('zfcUser@zfcUser.com');

        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn($this->user);

        $result = $this->helper->__invoke(null);

        static::assertEquals('zfcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName::setAuthService
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayName::getAuthService
     */
    public function testSetGetAuthService()
    {
        // We set the authservice in setUp, so we dont have to set it again
        static::assertSame($this->authService, $this->helper->getAuthService());
    }
}
