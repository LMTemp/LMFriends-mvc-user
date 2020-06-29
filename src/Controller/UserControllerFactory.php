<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Service\UserService;

/**
 * Class UserControllerFactory
 */
class UserControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return UserController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new UserController(
            $container->get(ModuleOptions::class),
            $container->get(UserService::class),
            $container->get(RedirectCallback::class),
            $container->get(Module::MVC_USER_FORM_LOGIN),
            $container->get(Module::MVC_USER_FORM_REGISTER),
            $container->get(Module::MVC_USER_FORM_CHANGE_PASSWORD),
            $container->get(Module::MVC_USER_FORM_CHANGE_EMAIL)
        );
    }
}
