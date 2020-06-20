<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Form;
use LaminasFriends\Mvc\User\Validator;

class ChangeEmailFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceManager, $requestedName, array $options = null)
    {
        $moduleOptions = $serviceManager->get('zfcuser_module_options');
        $form = new Form\ChangeEmailForm(null, $moduleOptions);

        $form->setInputFilter(new Form\ChangeEmailFilter(
            $moduleOptions,
            new Validator\NoRecordExists(
                [
                'mapper' => $serviceManager->get('zfcuser_user_mapper'),
                'key'    => 'email'
                ]
            )
        ));

        return $form;
    }
}
