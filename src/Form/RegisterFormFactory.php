<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Validator\NoRecordExists;

/**
 * Class RegisterFormFactory
 */
class RegisterFormFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return RegisterForm
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $moduleOptions = $container->get(ModuleOptions::class);
        $form = new RegisterForm(null, $moduleOptions);
        //$form->setCaptchaElement($sm->get('zfcuser_captcha_element'));
        $form->setHydrator($container->get(Module::MVC_USER_FORM_REGISTER_HYDRATOR));
        $form->setInputFilter(
            new RegisterFormFilter(
                new NoRecordExists('email', $container->get(UserMapper::class)),
                new NoRecordExists('username', $container->get(UserMapper::class)),
                $moduleOptions
            )
        );
        return $form;
    }
}
