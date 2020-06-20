<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\ChangePasswordForm as Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

class ChangePasswordFormTest extends TestCase
{
    /**
     * @covers \LaminasFriends\Mvc\User\Form\ChangePasswordForm::__construct
     */
    public function testConstruct()
    {
        $options = $this->createMock(AuthenticationOptionsInterface::class);

        $form = new Form(null, $options);

        $elements = $form->getElements();

        static::assertArrayHasKey('identity', $elements);
        static::assertArrayHasKey('credential', $elements);
        static::assertArrayHasKey('newCredential', $elements);
        static::assertArrayHasKey('newCredentialVerify', $elements);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Form\ChangePasswordForm::getAuthenticationOptions
     * @covers \LaminasFriends\Mvc\User\Form\ChangePasswordForm::setAuthenticationOptions
     */
    public function testSetGetAuthenticationOptions()
    {
        $options = $this->createMock(AuthenticationOptionsInterface::class);
        $form = new Form(null, $options);

        static::assertSame($options, $form->getAuthenticationOptions());
    }
}
