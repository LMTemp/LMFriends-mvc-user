<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Controller\Plugin;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class UserAuthenticationPlugin
 */
class UserAuthenticationPlugin extends AbstractPlugin
{
    protected AdapterInterface $authAdapter;
    protected AuthenticationServiceInterface $authService;

    /**
     * UserAuthenticationPlugin constructor.
     *
     * @param AdapterInterface               $authAdapter
     * @param AuthenticationServiceInterface $authService
     */
    public function __construct(AdapterInterface $authAdapter, AuthenticationServiceInterface $authService)
    {
        $this->authAdapter = $authAdapter;
        $this->authService = $authService;
    }

    /**
     * Proxy convenience method
     *
     * @return bool
     */
    public function hasIdentity(): bool
    {
        return $this->getAuthService()->hasIdentity();
    }

    /**
     * Proxy convenience method
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getAuthService()->getIdentity();
    }

    /**
     * Get authAdapter.
     *
     * @return AdapterInterface
     */
    public function getAuthAdapter(): AdapterInterface
    {
        return $this->authAdapter;
    }

    /**
     * Get authService.
     *
     * @return AuthenticationServiceInterface
     */
    public function getAuthService(): AuthenticationServiceInterface
    {
        return $this->authService;
    }
}
