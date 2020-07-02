<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Storage;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Mapper\UserMapper;

/**
 * Class DbStorageFactory
 */
class DbStorageFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return DbStorage
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DbStorage($container->get(UserMapper::class), new Session());
    }
}
