<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Authentication\Adapter;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result as AuthenticationResult;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Stdlib\ResponseInterface as Response;
use LaminasFriends\Mvc\User\Exception;

/**
 * Class AdapterChainService
 */
class AdapterChainService implements AdapterInterface, EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /** @var AdapterChainEvent|null */
    protected $event;

    /**
     * Returns the authentication result
     *
     * @return AuthenticationResult
     */
    public function authenticate(): AuthenticationResult
    {
        $e = $this->getEvent();

        $result = new AuthenticationResult(
            $e->getCode(),
            $e->getIdentity(),
            $e->getMessages()
        );

        $this->resetAdapters();

        return $result;
    }

    /**
     * prepareForAuthentication
     *
     * @param  Request $request
     * @return Response|bool
     * @throws Exception\AuthenticationEventException
     */
    public function prepareForAuthentication(Request $request)
    {
        $e = $this->getEvent();
        $e->setRequest($request);

        $e->setName('authenticate.pre');
        $this->getEventManager()->triggerEvent($e);

        $e->setName('authenticate');
        $result = $this->getEventManager()->triggerEventUntil(
            static function ($test) {
                return ($test instanceof Response);
            },
            $e
        );

        if ($result->stopped()) {
            if ($result->last() instanceof Response) {
                return $result->last();
            }

            throw new Exception\AuthenticationEventException(
                sprintf(
                    'Auth event was stopped without a response. Got "%s" instead',
                    is_object($result->last()) ? get_class($result->last()) : gettype($result->last())
                )
            );
        }

        if ($e->getIdentity()) {
            $e->setName('authenticate.success');
            $this->getEventManager()->triggerEvent($e);
            return true;
        }

        $e->setName('authenticate.fail');
        $this->getEventManager()->triggerEvent($e);

        return false;
    }

    /**
     * resetAdapters
     *
     * @return AdapterChainService
     */
    public function resetAdapters(): AdapterChainService
    {
        $sharedManager = $this->getEventManager()->getSharedManager();

        if ($sharedManager) {
            $listeners = $sharedManager->getListeners(['authenticate'], 'authenticate');
            foreach ($listeners as $listener) {
                if (is_array($listener) && $listener[0] instanceof ChainableAdapter) {
                    $listener[0]->getStorage()->clear();
                }
            }
        }

        return $this;
    }

    /**
     * logoutAdapters
     *
     * @return AdapterChainService
     */
    public function logoutAdapters(): AdapterChainService
    {
        //Adapters might need to perform additional cleanup after logout
        $e = $this->getEvent();
        $e->setName('logout');
        $this->getEventManager()->triggerEvent($e);

        return $this;
    }

    /**
     * Get the auth event
     *
     * @return AdapterChainEvent
     */
    public function getEvent(): AdapterChainEvent
    {
        if (null === $this->event) {
            $this->setEvent(new AdapterChainEvent());
            $this->event->setTarget($this);
        }

        return $this->event;
    }

    /**
     * Set an event to use during dispatch
     *
     * By default, will re-cast to AdapterChainEvent if another event type is provided.
     *
     * @param  EventInterface $e
     * @return AdapterChainService
     */
    public function setEvent(EventInterface $e)
    {
        if (!$e instanceof AdapterChainEvent) {
            $eventParams = $e->getParams();
            $e = new AdapterChainEvent();
            $e->setParams($eventParams);
        }

        $this->event = $e;

        return $this;
    }
}
