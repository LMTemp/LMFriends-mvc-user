<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Mapper;

use LaminasFriends\Mvc\User\Mapper\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use LaminasFriends\Mvc\User\Entity\UserEntity;
use LaminasFriends\Mvc\User\Mapper\UserHydrator as Hydrator;

class UserHydratorTest extends TestCase
{
    protected $hydrator;

    protected function setUp(): void
    {
        $hydrator = new Hydrator();
        $this->hydrator = $hydrator;
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Mapper\UserHydrator::extract
     *
     */
    public function testExtractWithInvalidUserObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new stdClass();
        $this->hydrator->extract($user);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Mapper\UserHydrator::extract
     * @dataProvider dataProviderTestExtractWithValidUserObject
     * @see https://github.com/ZF-Commons/ZfcUser/pull/421
     */
    public function testExtractWithValidUserObject($object, $expectArray)
    {
        $result = $this->hydrator->extract($object);
        static::assertEquals($expectArray, $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Mapper\UserHydrator::hydrate
     *
     */
    public function testHydrateWithInvalidUserObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new stdClass();
        $this->hydrator->hydrate([], $user);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Mapper\UserHydrator::hydrate
     */
    public function testHydrateWithValidUserObject()
    {
        $user = new UserEntity();

        $expectArray = [
            'username' => 'mvcuser',
            'email' => 'Mvc UserService',
            'display_name' => 'MvcUser',
            'password' => 'MvcUserPassword',
            'state' => 1,
            'id' => 1
        ];

        /** @var UserEntity $result */
        $result = $this->hydrator->hydrate($expectArray, $user);

        static::assertEquals($expectArray['username'], $result->getUsername());
        static::assertEquals($expectArray['email'], $result->getEmail());
        static::assertEquals($expectArray['display_name'], $result->getDisplayName());
        static::assertEquals($expectArray['password'], $result->getPassword());
        static::assertEquals($expectArray['state'], $result->getState());
        static::assertEquals($expectArray['id'], $result->getId());
    }

    public function dataProviderTestExtractWithValidUserObject()
    {
        $createUserObject = static function ($data) {
            $user = new UserEntity();
            foreach ($data as $key => $value) {
                $methode = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                $user->$methode($value);
            }
            return $user;
        };
        $return = [];
        $expectArray = [];

        $buffer = [
            'username' => 'mvcuser',
            'email' => 'Mvc UserService',
            'display_name' => 'MvcUser',
            'password' => 'MvcUserPassword',
            'state' => 1,
            'id' => 1
        ];

        $return[]= [$createUserObject($buffer), $buffer];

        /**
         * @see https://github.com/ZF-Commons/ZfcUser/pull/421
         */
        $buffer = [
            'username' => 'mvcuser',
            'email' => 'Mvc UserService',
            'display_name' => 'MvcUser',
            'password' => 'MvcUserPassword',
            'state' => 1
        ];

        $return[]= [$createUserObject($buffer), $buffer];

        return $return;
    }
}
