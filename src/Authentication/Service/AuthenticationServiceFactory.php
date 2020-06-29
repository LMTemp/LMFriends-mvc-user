<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Service;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainService;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorage;

/**
 * Class AuthenticationServiceFactory
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AuthenticationService(
            $container->get(DbStorage::class),
            $container->get(AdapterChainService::class)
        );
    }
}
