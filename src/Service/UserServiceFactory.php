<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Service\UserService;

class UserServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $service = new UserService();
        $service->setServiceManager($serviceLocator);

        return $service;
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
