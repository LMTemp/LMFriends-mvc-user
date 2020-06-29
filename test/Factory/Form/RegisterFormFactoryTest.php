<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Factory\Form;

use Laminas\Form\FormElementManager\FormElementManagerV3Polyfill;
use Laminas\Hydrator\ClassMethodsHydrator;
use LaminasFriends\Mvc\User\Module;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use LaminasFriends\Mvc\User\Form\RegisterFormFactory as RegisterFactory;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Mapper\UserMapper as UserMapper;
use LaminasFriends\Mvc\User\Form\RegisterForm;

class RegisterFormFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(ModuleOptions::class, new ModuleOptions());
        $serviceManager->setService(UserMapper::class, new UserMapper());
        $serviceManager->setService(Module::MVC_USER_FORM_REGISTER_HYDRATOR, new ClassMethodsHydrator());

        $formElementManager = new FormElementManagerV3Polyfill($serviceManager);
        $serviceManager->setService('FormElementManager', $formElementManager);

        $factory = new RegisterFactory();

        static::assertInstanceOf(RegisterForm::class, $factory->__invoke($serviceManager, RegisterForm::class));
    }
}
