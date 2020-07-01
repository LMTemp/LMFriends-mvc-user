<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

/**
 * Interface UserControllerOptionsInterface
 */
interface UserControllerOptionsInterface
{
    /**
     * set use redirect param if present
     *
     * @param bool $useRedirectParameterIfPresent
     * @return void
     */
    public function setUseRedirectParameterIfPresent(bool $useRedirectParameterIfPresent): void;

    /**
     * get use redirect param if present
     *
     * @return bool
     */
    public function getUseRedirectParameterIfPresent(): bool;
}
