<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Storage;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DbStorageFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $db = new DbStorage();
        $db->setServiceManager($container);

        return $db;
    }
}
