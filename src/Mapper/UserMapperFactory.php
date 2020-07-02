<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

/**
 * Class UserMapperFactory
 */
class UserMapperFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return UserMapper
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $moduleOptions = $container->get(ModuleOptions::class);
        $entityClass = $moduleOptions->getUserEntityClass();

        $mapper = new UserMapper();
        $mapper->setDbAdapter($container->get(Module::MVC_USER_DB_ADAPTER));
        $mapper->setTableName($moduleOptions->getTableName());
        $mapper->setEntityPrototype(new $entityClass());
        $mapper->setHydrator(new UserHydrator());

        return $mapper;
    }
}
