<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Factory\Form;

use Laminas\Form\FormElementManager\FormElementManagerV3Polyfill;
use Laminas\Hydrator\ClassMethodsHydrator;
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
        $serviceManager->setService('zfcuser_module_options', new ModuleOptions());
        $serviceManager->setService('zfcuser_user_mapper', new UserMapper());
        $serviceManager->setService('zfcuser_register_form_hydrator', new ClassMethodsHydrator());

        $formElementManager = new FormElementManagerV3Polyfill($serviceManager);
        $serviceManager->setService('FormElementManager', $formElementManager);

        $factory = new RegisterFactory();

        static::assertInstanceOf(RegisterForm::class, $factory->__invoke($serviceManager, RegisterForm::class));
    }
}
