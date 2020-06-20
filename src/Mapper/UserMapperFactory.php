<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasFriends\Mvc\User\Mapper;
use LaminasFriends\Mvc\User\Options\ModuleOptions;

class UserMapperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('zfcuser_module_options');
        $dbAdapter = $serviceLocator->get('zfcuser_zend_db_adapter');

        $entityClass = $moduleOptions->getUserEntityClass();
        $tableName = $moduleOptions->getTableName();

        $mapper = new Mapper\UserMapper();
        $mapper->setDbAdapter($dbAdapter);
        $mapper->setTableName($tableName);
        $mapper->setEntityPrototype(new $entityClass());
        $mapper->setHydrator(new Mapper\UserHydrator());

        return $mapper;
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
