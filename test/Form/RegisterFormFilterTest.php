<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\RegisterFormFilter as Filter;
use LaminasFriends\Mvc\User\Validator\NoRecordExists;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

class RegisterFormFilterTest extends TestCase
{
    /**
     * @covers \LaminasFriends\Mvc\User\Form\RegisterFormFilter::__construct
     */
    public function testConstruct()
    {
        $options = $this->createMock(ModuleOptions::class);
        $options->expects(static::once())
                ->method('getEnableUsername')
                ->willReturn(true);
        $options->expects(static::once())
                ->method('getEnableDisplayName')
                ->willReturn(true);

        $emailValidator = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();
        $usernameValidator = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();

        $filter = new Filter($emailValidator, $usernameValidator, $options);

        $inputs = $filter->getInputs();
        static::assertArrayHasKey('username', $inputs);
        static::assertArrayHasKey('email', $inputs);
        static::assertArrayHasKey('display_name', $inputs);
        static::assertArrayHasKey('password', $inputs);
        static::assertArrayHasKey('passwordVerify', $inputs);
    }
}
