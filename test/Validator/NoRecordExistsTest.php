<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Validator;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Validator\AbstractRecord;
use LaminasFriends\Mvc\User\Validator\NoRecordExists as Validator;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;

class NoRecordExistsTest extends TestCase
{
    protected $validator;

    protected $mapper;

    protected function setUp(): void
    {
        $options = ['key' => 'username'];
        $validator = new Validator($options);
        $this->validator = $validator;

        $mapper = $this->createMock(UserMapperInterface::class);
        $this->mapper = $mapper;

        $validator->setMapper($mapper);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\NoRecordExists::isValid
     */
    public function testIsValid()
    {
        $this->mapper->expects(static::once())
                     ->method('findByUsername')
                     ->with('zfcUser')
                     ->willReturn(false);

        $result = $this->validator->isValid('zfcUser');
        static::assertTrue($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\NoRecordExists::isValid
     */
    public function testIsInvalid()
    {
        $this->mapper->expects(static::once())
                     ->method('findByUsername')
                     ->with('zfcUser')
                     ->willReturn('zfcUser');

        $result = $this->validator->isValid('zfcUser');
        static::assertFalse($result);

        $options = $this->validator->getOptions();
        static::assertArrayHasKey(AbstractRecord::ERROR_RECORD_FOUND, $options['messages']);
        static::assertEquals($options['messageTemplates'][AbstractRecord::ERROR_RECORD_FOUND], $options['messages'][AbstractRecord::ERROR_RECORD_FOUND]);
    }
}
