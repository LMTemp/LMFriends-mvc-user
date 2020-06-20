<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Options;

class ModuleOptionsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $config = $serviceLocator->get('Config');

        return new Options\ModuleOptions($config['zfcuser'] ?? []);
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
