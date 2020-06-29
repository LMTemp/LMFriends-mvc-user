<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Validator;

use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Validator\AbstractRecord;
use LaminasFriends\Mvc\User\Validator\RecordExists;
use LaminasFriends\Mvc\User\Mapper\UserMapperInterface;

class RecordExistsTest extends TestCase
{
    protected $validator;

    protected UserMapperInterface $mapper;

    protected function setUp(): void
    {
        $this->mapper = $this->createMock(UserMapperInterface::class);
        $this->validator = new RecordExists('username', $this->mapper);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\RecordExists::isValid
     */
    public function testIsValid()
    {
        $this->mapper->expects(static::once())
                     ->method('findByUsername')
                     ->with('mvcUser')
                     ->willReturn('mvcUser');

        $result = $this->validator->isValid('mvcUser');
        static::assertTrue($result);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Validator\RecordExists::isValid
     */
    public function testIsInvalid()
    {
        $this->mapper->expects(static::once())
                     ->method('findByUsername')
                     ->with('mvcUser')
                     ->willReturn(false);

        $result = $this->validator->isValid('mvcUser');
        static::assertFalse($result);

        $options = $this->validator->getOptions();
        static::assertArrayHasKey(AbstractRecord::ERROR_NO_RECORD_FOUND, $options['messages']);
        static::assertEquals(
            $options['messageTemplates'][AbstractRecord::ERROR_NO_RECORD_FOUND],
            $options['messages'][AbstractRecord::ERROR_NO_RECORD_FOUND]
        );
    }
}
