<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\View\Helper;

use Laminas\Authentication\AuthenticationService;
use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\View\Helper\ZfcUserIdentity as ViewHelper;

class ZfcUserIdentityTest extends TestCase
{
    protected $helper;

    protected $authService;

    protected function setUp(): void
    {
        $helper = new ViewHelper();
        $this->helper = $helper;

        $authService = $this->createMock(AuthenticationService::class);
        $this->authService = $authService;

        $helper->setAuthService($authService);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserIdentity::__invoke
     */
    public function testInvokeWithIdentity()
    {
        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn('zfcUser');

        $result = $this->helper->__invoke();

        static::assertEquals('zfcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserIdentity::__invoke
     */
    public function testInvokeWithoutIdentity()
    {
        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(false);

        $result = $this->helper->__invoke();

        static::assertFalse($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserIdentity::setAuthService
     * @covers \LaminasFriends\Mvc\User\View\Helper\ZfcUserIdentity::getAuthService
     */
    public function testSetGetAuthService()
    {
        //We set the authservice in setUp, so we dont have to set it again
        static::assertSame($this->authService, $this->helper->getAuthService());
    }
}
