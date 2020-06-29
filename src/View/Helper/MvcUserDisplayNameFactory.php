<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\View;

/**
 * Class MvcUserDisplayNameFactory
 */
class MvcUserDisplayNameFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return MvcUserDisplayName
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MvcUserDisplayName($container->get(Module::MVC_USER_AUTH_SERVICE));
    }
}
