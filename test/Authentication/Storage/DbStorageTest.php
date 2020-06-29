<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Storage;

use LaminasFriends\Mvc\User\Authentication\Storage\DbStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Laminas\Authentication\Storage\Session;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;

/**
 * Class DbStorageTest
 */
class DbStorageTest extends TestCase
{
    protected DbStorage $db;
    /** @var Session|MockObject  */
    protected $storage;
    /** @var UserMapperInterface|MockObject  */
    protected $mapper;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(Session::class);
        $this->mapper = $this->createMock(UserMapperInterface::class);
        $this->db = new DbStorage($this->mapper, $this->storage);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::isEmpty
     */
    public function testIsEmpty()
    {
        $this->storage->expects(static::once())
                      ->method('isEmpty')
                      ->willReturn(true);

        static::assertTrue($this->db->isEmpty());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::read
     */
    public function testReadWithResolvedEntitySet()
    {
        $reflectionClass = new ReflectionClass(DbStorage::class);
        $reflectionProperty = $reflectionClass->getProperty('resolvedIdentity');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->db, 'mvcUser');

        static::assertSame('mvcUser', $this->db->read());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::read
     */
    public function testReadWithoutResolvedEntitySetIdentityIntUserFound()
    {
        $this->storage->expects(static::once())
                      ->method('read')
                      ->willReturn(1);

        $user = $this->createMock(\LaminasFriends\Mvc\User\Entity\UserEntity::class);
        $user->setUsername('mvcUser');

        $this->mapper->expects(static::once())
                     ->method('findById')
                     ->with(1)
                     ->willReturn($user);

        $result = $this->db->read();

        static::assertSame($user, $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::read
     */
    public function testReadWithoutResolvedEntitySetIdentityIntUserNotFound()
    {
        $this->storage->expects(static::once())
                      ->method('read')
                      ->willReturn(1);

        $this->mapper->expects(static::once())
                     ->method('findById')
                     ->with(1)
                     ->willReturn(false);

        $result = $this->db->read();

        static::assertNull($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::read
     */
    public function testReadWithoutResolvedEntitySetIdentityObject()
    {
        $user = $this->createMock(\LaminasFriends\Mvc\User\Entity\UserEntity::class);
        $user->setUsername('mvcUser');

        $this->storage->expects(static::once())
                      ->method('read')
                      ->willReturn($user);

        $result = $this->db->read();

        static::assertSame($user, $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::write
     */
    public function testWrite()
    {
        $reflectionClass = new ReflectionClass(DbStorage::class);
        $reflectionProperty = $reflectionClass->getProperty('resolvedIdentity');
        $reflectionProperty->setAccessible(true);

        $this->storage->expects(static::once())
                      ->method('write')
                      ->with('mvcUser');

        $this->db->write('mvcUser');

        static::assertNull($reflectionProperty->getValue($this->db));
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::clear
     */
    public function testClear()
    {
        $reflectionClass = new ReflectionClass(DbStorage::class);
        $reflectionProperty = $reflectionClass->getProperty('resolvedIdentity');
        $reflectionProperty->setAccessible(true);

        $this->storage->expects(static::once())
            ->method('clear');

        $this->db->clear();

        static::assertNull($reflectionProperty->getValue($this->db));
    }
}
