<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

interface UserServiceOptionsInterface extends
    RegistrationOptionsInterface,
    AuthenticationOptionsInterface
{
    /**
     * set user entity class name
     *
     * @param string $userEntityClass
     * @return void
     */
    public function setUserEntityClass(string $userEntityClass): void;

    /**
     * get user entity class name
     *
     * @return string
     */
    public function getUserEntityClass(): string;

    /**
     * set user state usage for registration/login process
     *
     * @param bool $flag
     *
     * @return void
     */
    public function setEnableUserState(bool $flag): void;

    /**
     * get user state usage for registration/login process
     *
     * @return bool
     */
    public function getEnableUserState(): bool;

    /**
     * get default user state on registration
     *
     * @return int
     */
    public function getDefaultUserState(): int;
}
