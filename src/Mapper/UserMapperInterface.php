<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Mapper;

use Laminas\Db\Adapter\Driver\ResultInterface;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;

interface UserMapperInterface
{
    /**
     * @param $email
     *
     * @return UserEntityInterface
     */
    public function findByEmail(string $email);

    /**
     * @param string $username
     *
     * @return UserEntityInterface
     */
    public function findByUsername(string $username);

    /**
     * @param string|int $id
     *
     * @return UserEntityInterface
     */
    public function findById($id);

    /**
     * @param UserEntityInterface $user
     */
    public function insert(UserEntityInterface $user);

    /**
     * @param UserEntityInterface $user
     */
    public function update(UserEntityInterface $user);
}
