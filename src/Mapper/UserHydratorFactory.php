<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class UserHydratorFactory
 */
class UserHydratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ClassMethodsHydrator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ClassMethodsHydrator();
    }
}
