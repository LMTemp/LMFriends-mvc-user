<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

/**
 * Class DbAdapterFactory
 */
class DbAdapterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return DbAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DbAdapter($container->get(UserMapper::class), $container->get(ModuleOptions::class));
    }
}
