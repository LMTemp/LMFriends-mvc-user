<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use LaminasFriends\Mvc\User\Options\FormOptionsInterface;
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
        $options = $this->createMock(FormOptionsInterface::class);

        $form = new Form(null, $options);

        $elements = $form->getElements();

        static::assertArrayHasKey('identity', $elements);
        static::assertArrayHasKey('credential', $elements);
        static::assertArrayHasKey('newCredential', $elements);
        static::assertArrayHasKey('newCredentialVerify', $elements);
        static::assertArrayHasKey('security', $elements);
    }
}
