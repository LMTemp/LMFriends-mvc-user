<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Entity;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Entity\UserEntity as Entity;

class UserEntityTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        $user = new Entity();
        $this->user = $user;
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::setId
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::getId
     */
    public function testSetGetId()
    {
        $this->user->setId(1);
        static::assertEquals(1, $this->user->getId());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::setUsername
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::getUsername
     */
    public function testSetGetUsername()
    {
        $this->user->setUsername('MvcUser');
        static::assertEquals('MvcUser', $this->user->getUsername());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::setDisplayName
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::getDisplayName
     */
    public function testSetGetDisplayName()
    {
        $this->user->setDisplayName('Mvc UserService');
        static::assertEquals('Mvc UserService', $this->user->getDisplayName());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::setEmail
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::getEmail
     */
    public function testSetGetEmail()
    {
        $this->user->setEmail('mvcUser@mvcUser.com');
        static::assertEquals('mvcUser@mvcUser.com', $this->user->getEmail());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::setPassword
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::getPassword
     */
    public function testSetGetPassword()
    {
        $this->user->setPassword('mvcUser');
        static::assertEquals('mvcUser', $this->user->getPassword());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::setState
     * @covers \LaminasFriends\Mvc\User\Entity\UserEntity::getState
     */
    public function testSetGetState()
    {
        $this->user->setState(1);
        static::assertEquals(1, $this->user->getState());
    }
}
