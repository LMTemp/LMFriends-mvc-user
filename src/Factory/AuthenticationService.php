<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorage;

class AuthenticationService implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        return new \Laminas\Authentication\AuthenticationService(
            $serviceLocator->get(DbStorage::class),
            $serviceLocator->get(AdapterChain::class)
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->__invoke($serviceLocator, null);
    }
}
