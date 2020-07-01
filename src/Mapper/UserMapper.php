<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Mapper;

use Laminas\Db\Adapter\Driver\ResultInterface;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;
use Laminas\Hydrator\HydratorInterface;

/**
 * Class UserMapper
 */
class UserMapper extends AbstractDbMapper implements UserMapperInterface
{
    protected string $tableName  = 'user';

    public function findByEmail(string $email)
    {
        $select = $this->getSelect()
                       ->where(['email' => $email]);
        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);

        return $entity;
    }

    public function findByUsername(string $username)
    {
        $select = $this->getSelect()
                       ->where(['username' => $username]);
        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);

        return $entity;
    }

    public function findById($id)
    {
        $select = $this->getSelect()
                       ->where(['id' => $id]);
        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);

        return $entity;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function insert(UserEntityInterface $entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        $result = parent::insert($entity, $tableName, $hydrator);

        $entity->setId($result->getGeneratedValue());

        return $result;
    }

    /**
     * @param UserEntityInterface    $entity
     * @param null                   $where
     * @param string|null            $tableName
     * @param HydratorInterface|null $hydrator
     *
     * @return ResultInterface
     */
    public function update(UserEntityInterface $entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = ['id' => $entity->getId()];
        }

        return parent::update($entity, $where, $tableName, $hydrator);
    }
}
