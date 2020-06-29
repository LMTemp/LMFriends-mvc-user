<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Storage;

use Laminas\Authentication\Exception\InvalidArgumentException;
use Laminas\Authentication\Storage;
use Laminas\Authentication\Storage\StorageInterface;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;

/**
 * Class DbStorage
 */
class DbStorage implements Storage\StorageInterface
{
    protected StorageInterface $storage;
    protected UserMapperInterface $mapper;

    /**
     * @var mixed
     */
    protected $resolvedIdentity;

    /**
     * DbStorage constructor.
     *
     * @param UserMapperInterface $userMapper
     * @param StorageInterface    $storage
     */
    public function __construct(UserMapperInterface $userMapper, StorageInterface $storage)
    {
        $this->mapper = $userMapper;
        $this->storage = $storage;
    }

    /**
     * Returns true if and only if storage is empty
     *
     * @throws InvalidArgumentException If it is impossible to determine whether
     * storage is empty or not
     * @return bool
     */
    public function isEmpty(): bool
    {
        if ($this->storage->isEmpty()) {
            return true;
        }
        $identity = $this->storage->read();
        if ($identity === null) {
            $this->clear();
            return true;
        }

        return false;
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws InvalidArgumentException If reading contents from storage is impossible
     * @return mixed
     */
    public function read()
    {
        if (null !== $this->resolvedIdentity) {
            return $this->resolvedIdentity;
        }

        $identity = $this->storage->read();

        if (is_int($identity) || is_scalar($identity)) {
            $identity = $this->mapper->findById($identity);
        }

        if ($identity) {
            $this->resolvedIdentity = $identity;
        } else {
            $this->resolvedIdentity = null;
        }

        return $this->resolvedIdentity;
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws InvalidArgumentException If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents): void
    {
        $this->resolvedIdentity = null;
        $this->storage->write($contents);
    }

    /**
     * Clears contents from storage
     *
     * @throws InvalidArgumentException If clearing contents from storage is impossible
     * @return void
     */
    public function clear(): void
    {
        $this->resolvedIdentity = null;
        $this->storage->clear();
    }
}
