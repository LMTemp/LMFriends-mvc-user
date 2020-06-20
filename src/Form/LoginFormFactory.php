<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Form;

class LoginFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceManager, $requestedName, array $options = null)
    {
        $moduleOptions = $serviceManager->get('zfcuser_module_options');
        $form = new Form\LoginForm(null, $moduleOptions);

        $form->setInputFilter(new Form\LoginFilter($moduleOptions));

        return $form;
    }
}
