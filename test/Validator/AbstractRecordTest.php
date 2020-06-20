<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Validator;

use Exception;
use LaminasFriends\Mvc\User\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use LaminasFriendsTest\Mvc\User\Validator\TestAsset\AbstractRecordExtension;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;

class AbstractRecordTest extends TestCase
{
    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::__construct
     */
    public function testConstructEmptyArray()
    {
        $this->expectExceptionMessage('No key provided');
        $this->expectException(InvalidArgumentException::class);
        $options = [];
        new AbstractRecordExtension($options);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::getMapper
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::setMapper
     */
    public function testGetSetMapper()
    {
        $options = ['key' => ''];
        $validator = new AbstractRecordExtension($options);

        static::assertNull($validator->getMapper());

        $mapper = $this->createMock(UserMapperInterface::class);
        $validator->setMapper($mapper);
        static::assertSame($mapper, $validator->getMapper());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::getKey
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::setKey
     */
    public function testGetSetKey()
    {
        $options = ['key' => 'username'];
        $validator = new AbstractRecordExtension($options);

        static::assertEquals('username', $validator->getKey());

        $validator->setKey('email');
        static::assertEquals('email', $validator->getKey());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::query
     *
     *
     */
    public function testQueryWithInvalidKey()
    {
        $this->expectExceptionMessage('Invalid key used in ZfcUser validator');
        $this->expectException(Exception::class);
        $options = ['key' => 'zfcUser'];
        $validator = new AbstractRecordExtension($options);

        $method = new ReflectionMethod(AbstractRecordExtension::class, 'query');
        $method->setAccessible(true);

        $method->invoke($validator, ['test']);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::query
     */
    public function testQueryWithKeyUsername()
    {
        $options = ['key' => 'username'];
        $validator = new AbstractRecordExtension($options);

        $mapper = $this->createMock(UserMapperInterface::class);
        $mapper->expects(static::once())
               ->method('findByUsername')
               ->with('test')
               ->willReturn('ZfcUser');

        $validator->setMapper($mapper);

        $method = new ReflectionMethod(AbstractRecordExtension::class, 'query');
        $method->setAccessible(true);

        $result = $method->invoke($validator, 'test');

        static::assertEquals('ZfcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::query
     */
    public function testQueryWithKeyEmail()
    {
        $options = ['key' => 'email'];
        $validator = new AbstractRecordExtension($options);

        $mapper = $this->createMock(UserMapperInterface::class);
        $mapper->expects(static::once())
            ->method('findByEmail')
            ->with('test@test.com')
            ->willReturn('ZfcUser');

        $validator->setMapper($mapper);

        $method = new ReflectionMethod(AbstractRecordExtension::class, 'query');
        $method->setAccessible(true);

        $result = $method->invoke($validator, 'test@test.com');

        static::assertEquals('ZfcUser', $result);
    }
}
