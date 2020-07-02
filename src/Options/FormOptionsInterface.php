<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

/**
 * Interface FormOptionsInterface
 */
interface FormOptionsInterface
{
    /**
     * get login form timeout in seconds
     *
     * @return int
     */
    public function getLoginFormTimeout(): int;

    /**
     * set login form timeout
     *
     * @param int $loginFormTimeout
     *
     * @return void
     */
    public function setLoginFormTimeout(int $loginFormTimeout): void;

    /**
     * get registration form timeout in seconds
     *
     * @return int
     */
    public function getRegistrationFormTimeout(): int;

    /**
     * set registration form timeout in seconds
     *
     * @param int $registrationFormTimeout
     *
     * @return void
     */
    public function setRegistrationFormTimeout(int $registrationFormTimeout): void;

    /**
     * get change email form timeout in seconds
     *
     * @return int
     */
    public function getChangeEmailFormTimeout(): int;

    /**
     * set change email form timeout in seconds
     *
     * @param int $changeEmailFormTimeout
     *
     * @return void
     */
    public function setChangeEmailFormTimeout(int $changeEmailFormTimeout): void;

    /**
     * get change pw form timeout in seconds
     *
     * @return int
     */
    public function getChangePasswordFormTimeout(): int;

    /**
     * set change pw form timeout in seconds
     *
     * @param int $changePasswordFormTimeout
     *
     * @return void
     */
    public function setChangePasswordFormTimeout(int $changePasswordFormTimeout): void;
}