<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Form;

class ChangePasswordFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceManager, $requestedName, array $options = null)
    {
        $moduleOptions = $serviceManager->get('zfcuser_module_options');
        $form = new Form\ChangePasswordForm(null, $moduleOptions);

        $form->setInputFilter(new Form\ChangePasswordFilter($moduleOptions));

        return $form;
    }
}
