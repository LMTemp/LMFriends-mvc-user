<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Factory\Form;

use Laminas\Form\FormElementManager\FormElementManagerV3Polyfill;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use LaminasFriends\Mvc\User\Form\ChangePasswordFormFactory as ChangePasswordFactory;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Mapper\UserMapper as UserMapper;
use LaminasFriends\Mvc\User\Form\ChangePasswordForm;

class ChangePasswordFormFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(ModuleOptions::class, new ModuleOptions());
        $serviceManager->setService(UserMapper::class, new UserMapper());

        $formElementManager = new FormElementManagerV3Polyfill($serviceManager);
        $serviceManager->setService('FormElementManager', $formElementManager);

        $factory = new ChangePasswordFactory();

        static::assertInstanceOf(ChangePasswordForm::class, $factory->__invoke($serviceManager, ChangePasswordForm::class));
    }
}
