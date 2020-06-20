<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\ChangeEmailForm as Form;
use LaminasFriends\Mvc\User\Options\AuthenticationOptionsInterface;

class ChangeEmailFormTest extends TestCase
{
    /**
     * @covers \LaminasFriends\Mvc\User\Form\ChangeEmailForm::__construct
     */
    public function testConstruct()
    {
        $options = $this->createMock(AuthenticationOptionsInterface::class);

        $form = new Form(null, $options);

        $elements = $form->getElements();

        static::assertArrayHasKey('identity', $elements);
        static::assertArrayHasKey('newIdentity', $elements);
        static::assertArrayHasKey('newIdentityVerify', $elements);
        static::assertArrayHasKey('credential', $elements);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Form\ChangeEmailForm::getAuthenticationOptions
     * @covers \LaminasFriends\Mvc\User\Form\ChangeEmailForm::setAuthenticationOptions
     */
    public function testSetGetAuthenticationOptions()
    {
        $options = $this->createMock(AuthenticationOptionsInterface::class);
        $form = new Form(null, $options);

        static::assertSame($options, $form->getAuthenticationOptions());
    }
}
