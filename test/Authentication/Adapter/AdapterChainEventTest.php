<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Adapter;

use Laminas\Stdlib\RequestInterface;
use PHPUnit\Framework\TestCase;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent;

class AdapterChainEventTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var AdapterChainEvent
     */
    protected $event;

    /**
     * Prepare the object to be tested.
     */
    protected function setUp(): void
    {
        $this->event = new AdapterChainEvent();
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent::getCode
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent::setCode
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent::getMessages
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent::setMessages
     */
    public function testCodeAndMessages()
    {
        $testCode = 103;
        $testMessages = ['Message recieved loud and clear.'];

        $this->event->setCode($testCode);
        static::assertEquals($testCode, $this->event->getCode(), 'Asserting code values match.');

        $this->event->setMessages($testMessages);
        static::assertEquals($testMessages, $this->event->getMessages(), 'Asserting messages values match.');
    }

    /**
     * @depends testCodeAndMessages
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent::getIdentity
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent::setIdentity
     */
    public function testIdentity()
    {
        $testCode = 123;
        $testMessages = ['The message.'];
        $testIdentity = 'the_user';

        $this->event->setCode($testCode);
        $this->event->setMessages($testMessages);

        $this->event->setIdentity($testIdentity);

        static::assertEquals($testCode, $this->event->getCode(), 'Asserting the code persisted.');
        static::assertEquals($testMessages, $this->event->getMessages(), 'Asserting the messages persisted.');
        static::assertEquals($testIdentity, $this->event->getIdentity(), 'Asserting the identity matches');

        $this->event->setIdentity();

        static::assertNull($this->event->getCode(), 'Asserting the code has been cleared.');
        static::assertEquals([], $this->event->getMessages(), 'Asserting the messages have been cleared.');
        static::assertNull($this->event->getIdentity(), 'Asserting the identity has been cleared');
    }

    public function testRequest()
    {
        $request = $this->createMock(RequestInterface::class);
        $this->event->setRequest($request);

        static::assertInstanceOf(RequestInterface::class, $this->event->getRequest());
    }
}
