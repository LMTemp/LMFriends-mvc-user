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
    private $userMapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userMapper = $this->createMock(UserMapperInterface::class);
    }


    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::query
     *
     *
     */
    public function testQueryWithInvalidKey()
    {
        $this->expectExceptionMessage('Invalid key used in MvcUser validator');
        $this->expectException(Exception::class);
        $validator = new AbstractRecordExtension('mvcUser', $this->userMapper);

        $method = new ReflectionMethod(AbstractRecordExtension::class, 'query');
        $method->setAccessible(true);

        $method->invoke($validator, ['test']);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::query
     */
    public function testQueryWithKeyUsername()
    {
        $validator = new AbstractRecordExtension('username', $this->userMapper);

        $this->userMapper->expects(static::once())
               ->method('findByUsername')
               ->with('test')
               ->willReturn('MvcUser');

        $method = new ReflectionMethod(AbstractRecordExtension::class, 'query');
        $method->setAccessible(true);

        $result = $method->invoke($validator, 'test');

        static::assertEquals('MvcUser', $result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\AbstractRecord::query
     */
    public function testQueryWithKeyEmail()
    {
        $validator = new AbstractRecordExtension('email', $this->userMapper);

        $this->userMapper->expects(static::once())
            ->method('findByEmail')
            ->with('test@test.com')
            ->willReturn('MvcUser');



        $method = new ReflectionMethod(AbstractRecordExtension::class, 'query');
        $method->setAccessible(true);

        $result = $method->invoke($validator, 'test@test.com');

        static::assertEquals('MvcUser', $result);
    }
}
