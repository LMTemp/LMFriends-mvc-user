<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Factory\Form;

use Laminas\Form\FormElementManager\FormElementManagerV3Polyfill;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use LaminasFriends\Mvc\User\Form\ChangeEmailFormFactory as ChangeEmailFactory;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Mapper\UserMapper as UserMapper;
use LaminasFriends\Mvc\User\Form\ChangeEmailForm;

class ChangeEmailFormFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager([
            'services' => [
                ModuleOptions::class => new ModuleOptions(),
                UserMapper::class => new UserMapper()
            ]
        ]);

        $formElementManager = new FormElementManagerV3Polyfill($serviceManager);
        $serviceManager->setService('FormElementManager', $formElementManager);

        $factory = new ChangeEmailFactory();

        static::assertInstanceOf(ChangeEmailForm::class, $factory->__invoke($serviceManager, ChangeEmailForm::class));
    }
}
