<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Form;
use LaminasFriends\Mvc\User\Validator;

class RegisterFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceManager, $requestedName, array $options = null)
    {
        $moduleOptions = $serviceManager->get('zfcuser_module_options');
        $form = new Form\RegisterForm(null, $moduleOptions);

        //$form->setCaptchaElement($sm->get('zfcuser_captcha_element'));
        $form->setHydrator($serviceManager->get('zfcuser_register_form_hydrator'));
        $form->setInputFilter(new Form\RegisterFilter(
            new Validator\NoRecordExists(
                [
                'mapper' => $serviceManager->get('zfcuser_user_mapper'),
                'key'    => 'email'
                ]
            ),
            new Validator\NoRecordExists(
                [
                'mapper' => $serviceManager->get('zfcuser_user_mapper'),
                'key'    => 'username'
                ]
            ),
            $moduleOptions
        ));

        return $form;
    }
}
