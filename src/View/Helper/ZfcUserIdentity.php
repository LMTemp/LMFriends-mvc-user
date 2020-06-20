<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Authentication\AuthenticationService;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;

class ZfcUserIdentity extends AbstractHelper
{
    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * __invoke
     *
     * @access public
     * @return UserEntityInterface
     */
    public function __invoke()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->getAuthService()->getIdentity();
        }

        return false;
    }

    /**
     * Get authService.
     *
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    /**
     * Set authService.
     *
     * @param AuthenticationService $authService
     * @return ZfcUserIdentity
     */
    public function setAuthService(AuthenticationService $authService)
    {
        $this->authService = $authService;
        return $this;
    }
}
