<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

/**
 * Interface RegistrationOptionsInterface
 */
interface RegistrationOptionsInterface
{
    /**
     * set enable display name
     *
     * @param bool $enableDisplayName
     * @return void
     */
    public function setEnableDisplayName(bool $enableDisplayName): void;

    /**
     * get enable display name
     *
     * @return bool
     */
    public function getEnableDisplayName(): bool;

    /**
     * set enable user registration
     *
     * @param bool $enableRegistration
     * @return void
     */
    public function setEnableRegistration(bool $enableRegistration): void;

    /**
     * get enable user registration
     *
     * @return bool
     */
    public function getEnableRegistration(): bool;

    /**
     * set enable username
     *
     * @param bool $enableUsername
     * @return void
     */
    public function setEnableUsername(bool $enableUsername): void;

    /**
     * get enable username
     *
     * @return bool
     */
    public function getEnableUsername(): bool;

    /**
     * set user form timeout in seconds
     *
     * @param int $userFormTimeout
     * @return void
     */
    public function setUserFormTimeout(int $userFormTimeout): void;

    /**
     * get user form timeout in seconds
     *
     * @return int
     */
    public function getUserFormTimeout(): int;

    /**
     * set use a captcha in registration form
     *
     * @param bool $useRegistrationFormCaptcha
     * @return void
     */
    public function setUseRegistrationFormCaptcha(bool $useRegistrationFormCaptcha): void;

    /**
     * get use a captcha in registration form
     *
     * @return bool
     */
    public function getUseRegistrationFormCaptcha(): bool;

    /**
     * set login after registration
     *
     * @param bool $loginAfterRegistration
     * @return void
     */
    public function setLoginAfterRegistration(bool $loginAfterRegistration): void;

    /**
     * get login after registration
     *
     * @return bool
     */
    public function getLoginAfterRegistration(): bool;

    /**
     * set form CAPTCHA options
     *
     * @param array $formCaptchaOptions
     * @return void
     */
    public function setFormCaptchaOptions(array $formCaptchaOptions): void;

    /**
     * get form CAPTCHA options
     *
     * @return array
     */
    public function getFormCaptchaOptions(): array;
}
