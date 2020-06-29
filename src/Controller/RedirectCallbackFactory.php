<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

/**
 * Class RedirectCallbackFactory
 */
class RedirectCallbackFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return RedirectCallback
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RedirectCallback(
            $container->get('Application'),
            $container->get('Router'),
            $container->get(ModuleOptions::class)
        );
    }
}
