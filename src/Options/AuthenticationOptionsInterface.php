<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

/**
 * Interface AuthenticationOptionsInterface
 */
interface AuthenticationOptionsInterface extends PasswordOptionsInterface
{

    /**
     * set login form timeout in seconds
     *
     * @param int $loginFormTimeout
     * @return void
     */
    public function setLoginFormTimeout(int $loginFormTimeout): void;

    /**
     * get login form timeout in seconds
     *
     * @return int
     */
    public function getLoginFormTimeout(): int;

    /**
     * set auth identity fields
     *
     * @param array $authIdentityFields
     * @return void
     */
    public function setAuthIdentityFields(array $authIdentityFields): void;

    /**
     * get auth identity fields
     *
     * @return array
     */
    public function getAuthIdentityFields(): array;
}
