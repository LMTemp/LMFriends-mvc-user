<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Authentication\AuthenticationService;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface as User;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;
use LaminasFriends\Mvc\User\Exception\DomainException;

class ZfcUserDisplayName extends AbstractHelper
{
    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * __invoke
     *
     * @access public
     *
     * @param UserEntityInterface $user
     *
     * @return String
     *@throws DomainException
     */
    public function __invoke(User $user = null)
    {
        if (null === $user) {
            if ($this->getAuthService()->hasIdentity()) {
                $user = $this->getAuthService()->getIdentity();
                if (!$user instanceof User) {
                    throw new DomainException(
                        '$user is not an instance of UserService',
                        500
                    );
                }
            } else {
                return false;
            }
        }

        $displayName = $user->getDisplayName();
        if (null === $displayName) {
            $displayName = $user->getUsername();
        }
        // UserService will always have an email, so we do not have to throw error
        if (null === $displayName) {
            $displayName = $user->getEmail();
            $displayName = substr($displayName, 0, strpos($displayName, '@'));
        }

        return $displayName;
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
     * @return ZfcUserDisplayName
     */
    public function setAuthService(AuthenticationService $authService)
    {
        $this->authService = $authService;
        return $this;
    }
}
