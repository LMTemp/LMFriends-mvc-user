<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Mapper;

use Laminas\Hydrator\ClassMethodsHydrator;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;
use LaminasFriends\Mvc\User\Mapper\Exception\InvalidArgumentException;

class UserHydrator extends ClassMethodsHydrator
{
    /**
     * Extract values from an object
     *
     * @param UserEntityInterface $object
     * @return array
     * @throws InvalidArgumentException
     */
    public function extract($object): array
    {
        if (!$object instanceof UserEntityInterface) {
            throw new InvalidArgumentException('$object must be an instance of ' . UserEntityInterface::class);
        }

        $data = parent::extract($object);
        if ($data['id'] === null) {
            unset($data['id']);
        }

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  UserEntityInterface $object
     * @return UserEntityInterface
     * @throws InvalidArgumentException
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof UserEntityInterface) {
            throw new InvalidArgumentException('$object must be an instance of ' . UserEntityInterface::class);
        }

        return parent::hydrate($data, $object);
    }
}
