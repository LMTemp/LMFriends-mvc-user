<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\View\Helper\AbstractHelper;

/**
 * Class MvcUserIdentity
 */
class MvcUserIdentity extends AbstractHelper
{
    protected AuthenticationServiceInterface $authService;

    /**
     * MvcUserIdentity constructor.
     *
     * @param AuthenticationServiceInterface $authService
     */
    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @return mixed|null
     */
    public function __invoke()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->getAuthService()->getIdentity();
        }

        return null;
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
