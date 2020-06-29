<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Adapter;

use PHPUnit\Framework\TestCase;
use Laminas\Authentication\Storage\Session;
use LaminasFriendsTest\Mvc\User\Authentication\Adapter\TestAsset\AbstractAdapterExtension;
use LaminasFriends\Mvc\User\Authentication\Adapter\AbstractAdapter;

class AbstractAdapterTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var AbstractAdapterExtension
     */
    protected $adapter;

    protected function setUp(): void
    {
        $this->adapter = new AbstractAdapterExtension();
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AbstractAdapter::getStorage
     */
    public function testGetStorageWithoutStorageSet()
    {
        static::assertInstanceOf(Session::class, $this->adapter->getStorage());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AbstractAdapter::getStorage
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AbstractAdapter::setStorage
     */
    public function testSetGetStorage()
    {
        $storage = new Session('MvcUser');
        $storage->write('mvcUser');
        $this->adapter->setStorage($storage);

        static::assertInstanceOf(Session::class, $this->adapter->getStorage());
        static::assertSame('mvcUser', $this->adapter->getStorage()->read());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AbstractAdapter::isSatisfied
     */
    public function testIsSatisfied()
    {
        static::assertFalse($this->adapter->isSatisfied());
    }

    public function testSetSatisfied()
    {
        $result = $this->adapter->setSatisfied();
        static::assertInstanceOf(AbstractAdapter::class, $result);
        static::assertTrue($this->adapter->isSatisfied());

        $result = $this->adapter->setSatisfied(false);
        static::assertInstanceOf(AbstractAdapter::class, $result);
        static::assertFalse($this->adapter->isSatisfied());
    }
}
