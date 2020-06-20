<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\LoginFilter as Filter;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use Laminas\Validator\EmailAddress;

class LoginFilterTest extends TestCase
{
    /**
     * @covers \LaminasFriends\Mvc\User\Form\LoginFilter::__construct
     */
    public function testConstruct()
    {
        $options = $this->createMock(ModuleOptions::class);
        $options->expects(static::once())
                ->method('getAuthIdentityFields')
                ->willReturn([]);

        $filter = new Filter($options);

        $inputs = $filter->getInputs();
        static::assertArrayHasKey('identity', $inputs);
        static::assertArrayHasKey('credential', $inputs);

        static::assertEquals(0, $inputs['identity']->getValidatorChain()->count());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Form\LoginFilter::__construct
     */
    public function testConstructIdentityEmail()
    {
        $options = $this->createMock(ModuleOptions::class);
        $options->expects(static::once())
                ->method('getAuthIdentityFields')
                ->willReturn(['email']);

        $filter = new Filter($options);

        $inputs = $filter->getInputs();
        static::assertArrayHasKey('identity', $inputs);
        static::assertArrayHasKey('credential', $inputs);

        $identity = $inputs['identity'];

        // test email as identity
        $validators = $identity->getValidatorChain()->getValidators();
        static::assertArrayHasKey('instance', $validators[0]);
        static::assertInstanceOf(EmailAddress::class, $validators[0]['instance']);
    }
}
