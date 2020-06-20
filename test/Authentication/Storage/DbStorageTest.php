<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Storage;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Laminas\Authentication\Storage\Session;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;

class DbStorageTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var DbStorage
     */
    protected $db;

    /**
     * Mock of Storage.
     *
     * @var storage
     */
    protected $storage;

    /**
     * Mock of Mapper.
     *
     * @var mapper
     */
    protected $mapper;

    protected function setUp(): void
    {
        $db = new DbStorage();
        $this->db = $db;

        $this->storage = $this->createMock(Session::class);
        $this->mapper = $this->createMock(UserMapper::class);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::isEmpty
     */
    public function testIsEmpty()
    {
        $this->storage->expects(static::once())
                      ->method('isEmpty')
                      ->willReturn(true);

        $this->db->setStorage($this->storage);

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
        $reflectionProperty->setValue($this->db, 'zfcUser');

        static::assertSame('zfcUser', $this->db->read());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::read
     */
    public function testReadWithoutResolvedEntitySetIdentityIntUserFound()
    {
        $this->storage->expects(static::once())
                      ->method('read')
                      ->willReturn(1);

        $this->db->setStorage($this->storage);

        $user = $this->createMock(\LaminasFriends\Mvc\User\Entity\UserEntity::class);
        $user->setUsername('zfcUser');

        $this->mapper->expects(static::once())
                     ->method('findById')
                     ->with(1)
                     ->willReturn($user);

        $this->db->setMapper($this->mapper);

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

        $this->db->setStorage($this->storage);

        $this->mapper->expects(static::once())
                     ->method('findById')
                     ->with(1)
                     ->willReturn(false);

        $this->db->setMapper($this->mapper);

        $result = $this->db->read();

        static::assertNull($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::read
     */
    public function testReadWithoutResolvedEntitySetIdentityObject()
    {
        $user = $this->createMock(\LaminasFriends\Mvc\User\Entity\UserEntity::class);
        $user->setUsername('zfcUser');

        $this->storage->expects(static::once())
                      ->method('read')
                      ->willReturn($user);

        $this->db->setStorage($this->storage);

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
                      ->with('zfcUser');

        $this->db->setStorage($this->storage);

        $this->db->write('zfcUser');

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

        $this->db->setStorage($this->storage);

        $this->db->clear();

        static::assertNull($reflectionProperty->getValue($this->db));
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::getMapper
     */
    public function testGetMapperWithNoMapperSet()
    {
        $sm = $this->createMock(ServiceManager::class);
        $sm->expects(static::once())
           ->method('get')
           ->with('zfcuser_user_mapper')
           ->willReturn($this->mapper);

        $this->db->setServiceManager($sm);

        static::assertInstanceOf(UserMapperInterface::class, $this->db->getMapper());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::setMapper
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::getMapper
     */
    public function testSetGetMapper()
    {
        $mapper = new UserMapper();
        $mapper->setTableName('zfcUser');

        $this->db->setMapper($mapper);

        static::assertInstanceOf(UserMapper::class, $this->db->getMapper());
        static::assertSame('zfcUser', $this->db->getMapper()->getTableName());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::setServiceManager
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::getServiceManager
     */
    public function testSetGetServicemanager()
    {
        $sm = $this->createMock(ServiceManager::class);

        $this->db->setServiceManager($sm);

        static::assertInstanceOf(ServiceLocatorInterface::class, $this->db->getServiceManager());
        static::assertSame($sm, $this->db->getServiceManager());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::getStorage
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::setStorage
     */
    public function testGetStorageWithoutStorageSet()
    {
        static::assertInstanceOf(Session::class, $this->db->getStorage());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::getStorage
     * @covers \LaminasFriends\Mvc\User\Authentication\Storage\DbStorage::setStorage
     */
    public function testSetGetStorage()
    {
        $storage = new Session('ZfcUserStorage');
        $this->db->setStorage($storage);

        static::assertInstanceOf(Session::class, $this->db->getStorage());
    }
}
