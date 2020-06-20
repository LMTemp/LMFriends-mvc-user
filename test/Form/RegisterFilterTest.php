<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\RegisterFilter as Filter;
use LaminasFriends\Mvc\User\Validator\NoRecordExists;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

class RegisterFilterTest extends TestCase
{
    /**
     * @covers \LaminasFriends\Mvc\User\Form\RegisterFilter::__construct
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

    public function testSetGetEmailValidator()
    {
        $options = $this->createMock(ModuleOptions::class);
        $validatorInit = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();
        $validatorNew = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();

        $filter = new Filter($validatorInit, $validatorInit, $options);

        static::assertSame($validatorInit, $filter->getEmailValidator());
        $filter->setEmailValidator($validatorNew);
        static::assertSame($validatorNew, $filter->getEmailValidator());
    }

    public function testSetGetUsernameValidator()
    {
        $options = $this->createMock(ModuleOptions::class);
        $validatorInit = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();
        $validatorNew = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();

        $filter = new Filter($validatorInit, $validatorInit, $options);

        static::assertSame($validatorInit, $filter->getUsernameValidator());
        $filter->setUsernameValidator($validatorNew);
        static::assertSame($validatorNew, $filter->getUsernameValidator());
    }

    public function testSetGetOptions()
    {
        $options = $this->createMock(ModuleOptions::class);
        $optionsNew = $this->createMock(ModuleOptions::class);
        $validatorInit = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();
        $filter = new Filter($validatorInit, $validatorInit, $options);

        static::assertSame($options, $filter->getOptions());
        $filter->setOptions($optionsNew);
        static::assertSame($optionsNew, $filter->getOptions());
    }
}
