<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\View\Helper;

use Laminas\Authentication\AuthenticationService;
use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\View\Helper\MvcUserIdentity;

class MvcUserIdentityTest extends TestCase
{
    protected $helper;

    protected $authService;

    protected function setUp(): void
    {
        $this->authService = $this->createMock(AuthenticationService::class);
        $this->helper = new MvcUserIdentity($this->authService);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserIdentity::__invoke
     */
    public function testInvokeWithIdentity()
    {
        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(true);
        $this->authService->expects(static::once())
                          ->method('getIdentity')
                          ->willReturn('mvcUser');

        $result = $this->helper->__invoke();

        static::assertEquals('mvcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\View\Helper\MvcUserIdentity::__invoke
     */
    public function testInvokeWithoutIdentity()
    {
        $this->authService->expects(static::once())
                          ->method('hasIdentity')
                          ->willReturn(false);

        static::assertNull($this->helper->__invoke());
    }
}
