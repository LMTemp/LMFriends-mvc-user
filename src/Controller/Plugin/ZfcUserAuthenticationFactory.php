<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Controller;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain;

class ZfcUserAuthenticationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $authService = $serviceLocator->get('zfcuser_auth_service');
        $authAdapter = $serviceLocator->get(AdapterChain::class);

        $controllerPlugin = new Controller\Plugin\ZfcUserAuthentication();
        $controllerPlugin->setAuthService($authService);
        $controllerPlugin->setAuthAdapter($authAdapter);

        return $controllerPlugin;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceManager
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $serviceLocator = $serviceManager->getServiceLocator();

        return $this->__invoke($serviceLocator, null);
    }
}
