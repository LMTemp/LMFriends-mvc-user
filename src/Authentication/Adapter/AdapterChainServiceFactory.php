<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

/**
 * Class AdapterChainServiceFactory
 */
class AdapterChainServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AdapterChainService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $chain = new AdapterChainService();
        $chain->setEventManager($container->get('EventManager'));

        $moduleOptions = $container->get(ModuleOptions::class);
        //iterate and attach multiple adapters and events if offered
        foreach ($moduleOptions->getAuthAdapters() as $priority => $adapterName) {
            $adapter = $container->get($adapterName);

            if (is_callable([$adapter, 'authenticate'])) {
                $chain->getEventManager()->attach('authenticate', [$adapter, 'authenticate'], $priority);
            }

            if (is_callable([$adapter, 'logout'])) {
                $chain->getEventManager()->attach('logout', [$adapter, 'logout'], $priority);
            }
        }
        return $chain;
    }
}
