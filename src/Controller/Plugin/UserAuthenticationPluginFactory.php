<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainService;
use LaminasFriends\Mvc\User\Module;

/**
 * Class UserAuthenticationPluginFactory
 */
class UserAuthenticationPluginFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return UserAuthenticationPlugin
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new UserAuthenticationPlugin(
            $container->get(Module::MVC_USER_AUTH_SERVICE),
            $container->get(AdapterChainService::class)
        );
    }
}
