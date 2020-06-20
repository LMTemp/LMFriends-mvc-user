<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\ChangeEmailFilter as Filter;
use LaminasFriends\Mvc\User\Validator\NoRecordExists;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use Laminas\Validator\EmailAddress;

class ChangeEmailFilterTest extends TestCase
{
    public function testConstruct()
    {
        $options = $this->createMock(ModuleOptions::class);
        $options->expects(static::once())
                ->method('getAuthIdentityFields')
                ->willReturn(['email']);

        $validator = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();
        $filter = new Filter($options, $validator);

        $inputs = $filter->getInputs();
        static::assertArrayHasKey('identity', $inputs);
        static::assertArrayHasKey('newIdentity', $inputs);
        static::assertArrayHasKey('newIdentityVerify', $inputs);

        $validators = $inputs['identity']->getValidatorChain()->getValidators();
        static::assertArrayHasKey('instance', $validators[0]);
        static::assertInstanceOf(EmailAddress::class, $validators[0]['instance']);
    }

    /**
     * @dataProvider providerTestConstructIdentityEmail
     */
    public function testConstructIdentityEmail($onlyEmail)
    {
        $options = $this->createMock(ModuleOptions::class);
        $options->expects(static::once())
                ->method('getAuthIdentityFields')
                ->willReturn(($onlyEmail) ? ['email'] : ['username']);

        $validator = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();
        $filter = new Filter($options, $validator);

        $inputs = $filter->getInputs();
        static::assertArrayHasKey('identity', $inputs);
        static::assertArrayHasKey('newIdentity', $inputs);
        static::assertArrayHasKey('newIdentityVerify', $inputs);

        $identity = $inputs['identity'];

        if ($onlyEmail !== false) {
            // test email as identity
            $validators = $identity->getValidatorChain()->getValidators();
            static::assertArrayHasKey('instance', $validators[0]);
            static::assertInstanceOf(EmailAddress::class, $validators[0]['instance']);
        } else {
            static::assertEquals(0, $inputs['identity']->getValidatorChain()->count());
        }
    }

    public function testSetGetEmailValidator()
    {
        $options = $this->createMock(ModuleOptions::class);
        $options->expects(static::once())
                ->method('getAuthIdentityFields')
                ->willReturn([]);

        $validatorInit = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();
        $validatorNew = $this->getMockBuilder(NoRecordExists::class)->disableOriginalConstructor()->getMock();

        $filter = new Filter($options, $validatorInit);

        static::assertSame($validatorInit, $filter->getEmailValidator());
        $filter->setEmailValidator($validatorNew);
        static::assertSame($validatorNew, $filter->getEmailValidator());
    }

    public function providerTestConstructIdentityEmail()
    {
        return [
            [true],
            [false]
        ];
    }
}
