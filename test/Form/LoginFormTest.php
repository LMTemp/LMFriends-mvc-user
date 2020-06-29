<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\LoginForm as Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

class LoginFormTest extends TestCase
{
    /**
     * @covers \LaminasFriends\Mvc\User\Form\LoginForm::__construct
     * @dataProvider providerTestConstruct
     */
    public function testConstruct($authIdentityFields = [])
    {
        $options = $this->createMock(AuthenticationOptionsInterface::class);
        $options->expects(static::once())
                ->method('getAuthIdentityFields')
                ->willReturn($authIdentityFields);

        $form = new Form(null, $options);

        $elements = $form->getElements();

        static::assertArrayHasKey('identity', $elements);
        static::assertArrayHasKey('credential', $elements);

        $expectedLabel= '';
        if (count($authIdentityFields) > 0) {
            foreach ($authIdentityFields as $field) {
                $expectedLabel .= ($expectedLabel === '') ? '' : ' or ';
                $expectedLabel .= ucfirst($field);
                static::assertStringContainsString(ucfirst($field), $elements['identity']->getLabel());
            }
        }

        static::assertEquals($expectedLabel, $elements['identity']->getLabel());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Form\LoginForm::getAuthenticationOptions
     */
    public function testGetAuthenticationOptions()
    {
        $options = $this->createMock(AuthenticationOptionsInterface::class);
        $options->expects(static::once())
                ->method('getAuthIdentityFields')
                ->willReturn([]);
        $form = new Form(null, $options);

        static::assertSame($options, $form->getAuthenticationOptions());
    }

    public function providerTestConstruct()
    {
        return [
            [[]],
            [['email']],
            [['username', 'email']],
        ];
    }
}
