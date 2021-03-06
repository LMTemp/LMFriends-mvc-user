<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

/**
 * Class UserServiceFactory
 */
class UserServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return UserService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new UserService(
            $container->get(ModuleOptions::class),
            $container->get(UserMapper::class),
            $container->get(Module::MVC_USER_AUTH_SERVICE)
        );
    }
}
