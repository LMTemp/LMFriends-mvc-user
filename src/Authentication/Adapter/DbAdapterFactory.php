<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DbAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $db = new DbAdapter();
        $db->setServiceManager($container);

        return $db;
    }
}
