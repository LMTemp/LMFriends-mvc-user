<?php

declare(strict_types=1);

namespace LaminasFriendsTest\Mvc\User\Authentication\Adapter;

use Laminas\Authentication\Result;
use Laminas\Stdlib\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ResponseCollection;
use Laminas\EventManager\SharedEventManagerInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainEvent;
use Laminas\Stdlib\RequestInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\ChainableAdapter;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorage;
use LaminasFriends\Mvc\User\Exception\AuthenticationEventException;

class AdapterChainTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var AdapterChain
     */
    protected $adapterChain;

    /**
     * Mock event manager.
     *
     * @var MockObject|EventManagerInterface
     */
    protected $eventManager;

    /**
     * Mock event manager.
     *
     * @var MockObject|SharedEventManagerInterface
     */
    protected $sharedEventManager;

    /**
     * For test where an event is required.
     *
     * @var MockObject|EventInterface
     */
    protected $event;

    /**
     * Used when testing prepareForAuthentication.
     *
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * Prepare the objects to be tested.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->event = null;
        $this->request = null;

        $this->adapterChain = new AdapterChain();

        $this->sharedEventManager = $this->createMock(SharedEventManagerInterface::class);
        //$this->sharedEventManager->expects($this->any())->method('getListeners')->will($this->returnValue([]));

        $this->eventManager = $this->createMock(EventManagerInterface::class);
        $this->eventManager->method('getSharedManager')->willReturn($this->sharedEventManager);
        $this->eventManager->method('setIdentifiers');

        $this->adapterChain->setEventManager($this->eventManager);
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::authenticate
     */
    public function testAuthenticate(): void
    {
        $event = $this->createMock(AdapterChainEvent::class);
        $event->expects(static::once())
              ->method('getCode')
              ->willReturn(123);
        $event->expects(static::once())
              ->method('getIdentity')
              ->willReturn('identity');
        $event->expects(static::once())
              ->method('getMessages')
              ->willReturn([]);

        $this->sharedEventManager->expects(static::once())
             ->method('getListeners')
             ->with(static::equalTo(['authenticate']), static::equalTo('authenticate'))
             ->willReturn([]);

        $this->adapterChain->setEvent($event);
        $result = $this->adapterChain->authenticate();

        static::assertInstanceOf(Result::class, $result);
        static::assertEquals('identity', $result->getIdentity());
        static::assertEquals([], $result->getMessages());
    }

    /**
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::resetAdapters
     */
    public function testResetAdapters(): void
    {
        $listeners = [];

        for ($i=1; $i<=3; $i++) {
            $storage = $this->createMock(DbStorage::class);
            $storage->expects(static::once())
                    ->method('clear');

            $adapter = $this->createMock(ChainableAdapter::class);
            $adapter->expects(static::once())
                    ->method('getStorage')
                    ->willReturn($storage);

            $callback = [$adapter, 'authenticate'];
            $listeners[] = $callback;
        }

        $this->sharedEventManager->expects(static::once())
             ->method('getListeners')
             ->with(static::equalTo(['authenticate']), static::equalTo('authenticate'))
             ->willReturn($listeners);

        $result = $this->adapterChain->resetAdapters();

        static::assertInstanceOf(AdapterChain::class, $result);
    }

    /**
     * Get through the first part of SetUpPrepareForAuthentication
     */
    protected function setUpPrepareForAuthentication()
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->event = $this->createMock(AdapterChainEvent::class);

        $this->event->expects(static::once())->method('setRequest')->with($this->request);
        $this->event->method('setName')->withConsecutive(['authenticate.pre'], ['authenticate']);

        $this->eventManager->expects(static::at(0))->method('triggerEvent')->with($this->event);

        /**
         * @var $response ResponseCollection
         */
        $responses = $this->createMock(ResponseCollection::class);

        $this->eventManager->expects(static::at(1))
            ->method('triggerEventUntil')
            ->willReturnCallback(
                static function ($callback, $event) use ($responses) {
                    if ($callback($responses->last())) {
                        $responses->setStopped(true);
                    }
                    return $responses;
                }
            );

        $this->adapterChain->setEvent($this->event);

        return $responses;
    }

    /**
     * Provider for testPrepareForAuthentication()
     *
     * @return array
     */
    public function identityProvider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * Tests prepareForAuthentication when falls through events.
     *
     * @param mixed $identity
     * @param bool  $expected
     *
     * @dataProvider identityProvider
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::prepareForAuthentication
     */
    public function testPrepareForAuthentication($identity, $expected): void
    {
        $result = $this->setUpPrepareForAuthentication();

        $result->expects(static::once())->method('stopped')->willReturn(false);

        $this->event->expects(static::once())->method('getIdentity')->willReturn($identity);

        static::assertEquals(
            $expected,
            $this->adapterChain->prepareForAuthentication($this->request),
            'Asserting prepareForAuthentication() returns true'
        );
    }

    /**
     * Test prepareForAuthentication() when the returned collection contains stopped.
     *
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::prepareForAuthentication
     */
    public function testPrepareForAuthenticationWithStoppedEvent(): void
    {
        $result = $this->setUpPrepareForAuthentication();

        $result->expects(static::once())->method('stopped')->willReturn(true);

        $lastResponse = $this->createMock(ResponseInterface::class);
        $result->expects(static::atLeastOnce())->method('last')->willReturn($lastResponse);

        static::assertEquals(
            $lastResponse,
            $this->adapterChain->prepareForAuthentication($this->request),
            'Asserting the Response returned from the event is returned'
        );
    }

    /**
     * Test prepareForAuthentication() when the returned collection contains stopped.
     *
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::prepareForAuthentication
     *
     */
    public function testPrepareForAuthenticationWithBadEventResult(): void
    {
        $this->expectException(AuthenticationEventException::class);
        $result = $this->setUpPrepareForAuthentication();

        $result->expects(static::once())->method('stopped')->willReturn(true);

        $lastResponse = 'random-value';
        $result->expects(static::atLeastOnce())->method('last')->willReturn($lastResponse);

        $this->adapterChain->prepareForAuthentication($this->request);
    }

    /**
     * Test getEvent() when no event has previously been set.
     *
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::getEvent
     */
    public function testGetEventWithNoEventSet(): void
    {
        $event = $this->adapterChain->getEvent();

        static::assertInstanceOf(
            AdapterChainEvent::class,
            $event,
            'Asserting the adapter in an instance of ' . AdapterChainEvent::class
        );
        static::assertEquals(
            $this->adapterChain,
            $event->getTarget(),
            'Asserting the Event target is the AdapterChain'
        );
    }

    /**
     * Test getEvent() when an event has previously been set.
     *
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::setEvent
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::getEvent
     */
    public function testGetEventWithEventSet(): void
    {
        $event = new AdapterChainEvent();

        $this->adapterChain->setEvent($event);

        static::assertEquals(
            $event,
            $this->adapterChain->getEvent(),
            'Asserting the event fetched is the same as the event set'
        );
    }

    /**
     * Tests the mechanism for casting one event type to AdapterChainEvent
     *
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::setEvent
     */
    public function testSetEventWithDifferentEventType(): void
    {
        $testParams = ['testParam' => 'testValue'];

        $event = new Event();
        $event->setParams($testParams);

        $this->adapterChain->setEvent($event);
        $returnEvent = $this->adapterChain->getEvent();

        static::assertInstanceOf(
            AdapterChainEvent::class,
            $returnEvent,
            'Asserting the adapter in an instance of ' . AdapterChainEvent::class
        );

        static::assertEquals(
            $testParams,
            $returnEvent->getParams(),
            'Asserting event parameters match'
        );
    }

    /**
     * Test the logoutAdapters method.
     *
     * @depends testGetEventWithEventSet
     * @covers \LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain::logoutAdapters
     */
    public function testLogoutAdapters(): void
    {
        $event = new AdapterChainEvent();

        $this->eventManager
            ->expects(static::once())
            ->method('triggerEvent')
            ->with($event);

        $this->adapterChain->setEvent($event);
        $this->adapterChain->logoutAdapters();
    }
}
