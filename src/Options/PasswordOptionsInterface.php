<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

/**
 * Interface PasswordOptionsInterface
 */
interface PasswordOptionsInterface
{
    /**
     * set password cost
     *
     * @param int $passwordCost
     * @return void
     */
    public function setPasswordCost(int $cost): void;

    /**
     * get password cost
     *
     * @return int
     */
    public function getPasswordCost(): int;
}
