<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\View\Helper;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\View\Helper\AbstractHelper;
use LaminasFriends\Mvc\User\Entity\UserEntityInterface;
use LaminasFriends\Mvc\User\Exception\DomainException;

/**
 * Class MvcUserDisplayName
 */
class MvcUserDisplayName extends AbstractHelper
{
    protected AuthenticationServiceInterface $authService;

    /**
     * MvcUserDisplayName constructor.
     *
     * @param AuthenticationServiceInterface $authService
     */
    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param UserEntityInterface|null $user
     *
     * @return string|null
     * @throws DomainException
     */
    public function __invoke(UserEntityInterface $user = null): ?string
    {
        if (null === $user) {
            if ($this->getAuthService()->hasIdentity()) {
                $user = $this->getAuthService()->getIdentity();
                if (!$user instanceof UserEntityInterface) {
                    throw new DomainException(
                        '$user is not an instance of '.UserEntityInterface::class,
                        500
                    );
                }
            } else {
                return null;
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
     * @return AuthenticationServiceInterface
     */
    public function getAuthService(): AuthenticationServiceInterface
    {
        return $this->authService;
    }
}
