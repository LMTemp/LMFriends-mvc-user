<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Validator\NoRecordExists;

/**
 * Class ChangeEmailFormFactory
 */
class ChangeEmailFormFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ChangeEmailForm
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $moduleOptions = $container->get(ModuleOptions::class);
        $form = new ChangeEmailForm(null, $moduleOptions);
        $form->setInputFilter(
            new ChangeEmailFormFilter(
                $moduleOptions,
                new NoRecordExists('email', $container->get(UserMapper::class))
            )
        );
        return $form;
    }
}
