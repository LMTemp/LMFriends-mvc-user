<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Controller;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Application;
use Laminas\Router\RouteInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Controller\RedirectCallback;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

class RedirectCallbackFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        /* @var RouteInterface $router */
        $router = $serviceLocator->get('Router');

        /* @var Application $application */
        $application = $serviceLocator->get('Application');

        /* @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('zfcuser_module_options');

        return new RedirectCallback($application, $router, $moduleOptions);
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
