<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Form;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Form\Base as Form;

class BaseTest extends TestCase
{
    public function testConstruct()
    {
        $form = new Form();

        $elements = $form->getElements();

        static::assertArrayHasKey('username', $elements);
        static::assertArrayHasKey('email', $elements);
        static::assertArrayHasKey('display_name', $elements);
        static::assertArrayHasKey('password', $elements);
        static::assertArrayHasKey('passwordVerify', $elements);
        static::assertArrayHasKey('submit', $elements);
        static::assertArrayHasKey('userId', $elements);
    }
}
