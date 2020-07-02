<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

use Laminas\Captcha\Figlet;
use Laminas\Stdlib\AbstractOptions;
use LaminasFriends\Mvc\User\Entity\UserEntity;
use LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter;
use LaminasFriends\Mvc\User\Module;

/**
 * Class ModuleOptions
 */
class ModuleOptions extends AbstractOptions implements
    AuthenticationOptionsInterface,
    RegistrationOptionsInterface,
    UserControllerOptionsInterface,
    UserServiceOptionsInterface
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;
    protected bool $useRedirectParameterIfPresent = true;
    protected string $loginRedirectRoute = Module::ROUTE_BASE;
    protected string $logoutRedirectRoute = Module::ROUTE_LOGIN;
    protected int $loginFormTimeout = 300;
    protected int $registrationFormTimeout = 300;
    protected int $changeEmailFormTimeout = 300;
    protected int $changePasswordFormTimeout = 300;
    protected bool $loginAfterRegistration = true;
    protected bool $enableUserState = false;
    protected int $defaultUserState = 1;
    protected array $allowedLoginStates = [null, 1];
    protected array $authAdapters = [100 => DbAdapter::class];
    protected array $authIdentityFields = ['email'];
    protected string $userEntityClass = UserEntity::class;
    protected string $userLoginWidgetViewTemplate = 'mvc-user/user/login.phtml';
    protected bool $enableRegistration = true;
    protected bool $enableUsername = false;
    protected bool $enableDisplayName = false;
    protected bool $useRegistrationFormCaptcha = false;
    protected int $passwordCost = 14;
    protected string $tableName = 'user';
    protected array $formCaptchaOptions = [
        'class'   => Figlet::class,
        'options' => [
            'wordLen'    => 5,
            'expiration' => 300,
            'timeout'    => 300,
        ],
    ];

    /**
     * get login redirect route
     *
     * @return string
     */
    public function getLoginRedirectRoute(): string
    {
        return $this->loginRedirectRoute;
    }

    /**
     * set login redirect route
     *
     * @param string $loginRedirectRoute
     *
     * @return void
     */
    public function setLoginRedirectRoute(string $loginRedirectRoute): void
    {
        $this->loginRedirectRoute = $loginRedirectRoute;
    }

    /**
     * get logout redirect route
     *
     * @return string
     */
    public function getLogoutRedirectRoute(): string
    {
        return $this->logoutRedirectRoute;
    }

    /**
     * set logout redirect route
     *
     * @param string $logoutRedirectRoute
     *
     * @return void
     */
    public function setLogoutRedirectRoute(string $logoutRedirectRoute): void
    {
        $this->logoutRedirectRoute = $logoutRedirectRoute;
    }

    /**
     * get use redirect param if present
     *
     * @return bool
     */
    public function getUseRedirectParameterIfPresent(): bool
    {
        return $this->useRedirectParameterIfPresent;
    }

    /**
     * set use redirect param if present
     *
     * @param bool $useRedirectParameterIfPresent
     *
     * @return void
     */
    public function setUseRedirectParameterIfPresent(bool $useRedirectParameterIfPresent): void
    {
        $this->useRedirectParameterIfPresent = $useRedirectParameterIfPresent;
    }

    /**
     * get the view template for the user login widget
     *
     * @return string
     */
    public function getUserLoginWidgetViewTemplate(): string
    {
        return $this->userLoginWidgetViewTemplate;
    }

    /**
     * set the view template for the user login widget
     *
     * @param string $userLoginWidgetViewTemplate
     *
     * @return void
     */
    public function setUserLoginWidgetViewTemplate(string $userLoginWidgetViewTemplate): void
    {
        $this->userLoginWidgetViewTemplate = $userLoginWidgetViewTemplate;
    }

    /**
     * get enable user registration
     *
     * @return bool
     */
    public function getEnableRegistration(): bool
    {
        return $this->enableRegistration;
    }

    /**
     * set enable user registration
     *
     * @param bool $enableRegistration
     *
     * @return void
     */
    public function setEnableRegistration(bool $enableRegistration): void
    {
        $this->enableRegistration = $enableRegistration;
    }

    /**
     * get login form timeout in seconds
     *
     * @return int
     */
    public function getLoginFormTimeout(): int
    {
        return $this->loginFormTimeout;
    }

    /**
     * set login form timeout
     *
     * @param int $loginFormTimeout
     *
     * @return void
     */
    public function setLoginFormTimeout(int $loginFormTimeout): void
    {
        $this->loginFormTimeout = $loginFormTimeout;
    }

    /**
     * get registration form timeout in seconds
     *
     * @return int
     */
    public function getRegistrationFormTimeout(): int
    {
        return $this->registrationFormTimeout;
    }

    /**
     * set registration form timeout in seconds
     *
     * @param int $registrationFormTimeout
     *
     * @return void
     */
    public function setRegistrationFormTimeout(int $registrationFormTimeout): void
    {
        $this->registrationFormTimeout = $registrationFormTimeout;
    }

    /**
     * get change email form timeout in seconds
     *
     * @return int
     */
    public function getChangeEmailFormTimeout(): int
    {
        return $this->changeEmailFormTimeout;
    }

    /**
     * set change email form timeout in seconds
     *
     * @param int $changeEmailFormTimeout
     *
     * @return void
     */
    public function setChangeEmailFormTimeout(int $changeEmailFormTimeout): void
    {
        $this->changeEmailFormTimeout = $changeEmailFormTimeout;
    }

    /**
     * get change pw form timeout in seconds
     *
     * @return int
     */
    public function getChangePasswordFormTimeout(): int
    {
        return $this->changePasswordFormTimeout;
    }

    /**
     * set change pw form timeout in seconds
     *
     * @param int $changePasswordFormTimeout
     *
     * @return void
     */
    public function setChangePasswordFormTimeout(int $changePasswordFormTimeout): void
    {
        $this->changePasswordFormTimeout = $changePasswordFormTimeout;
    }

    /**
     * get login after registration
     *
     * @return bool
     */
    public function getLoginAfterRegistration(): bool
    {
        return $this->loginAfterRegistration;
    }

    /**
     * set login after registration
     *
     * @param bool $loginAfterRegistration
     *
     * @return void
     */
    public function setLoginAfterRegistration(bool $loginAfterRegistration): void
    {
        $this->loginAfterRegistration = $loginAfterRegistration;
    }

    /**
     * get user state usage for registration/login process
     *
     * @return bool
     */
    public function getEnableUserState(): bool
    {
        return $this->enableUserState;
    }

    /**
     * set user state usage for registration/login process
     *
     * @param bool $flag
     *
     * @return void
     */
    public function setEnableUserState(bool $flag): void
    {
        $this->enableUserState = $flag;
    }

    /**
     * get default user state on registration
     *
     * @return int
     */
    public function getDefaultUserState(): int
    {
        return $this->defaultUserState;
    }

    /**
     * set default user state on registration
     *
     * @param int $state
     *
     * @return void
     */
    public function setDefaultUserState(int $state): void
    {
        $this->defaultUserState = $state;
    }

    /**
     * get list of states to allow user login
     *
     * @return array
     */
    public function getAllowedLoginStates(): array
    {
        return $this->allowedLoginStates;
    }

    /**
     * set list of states to allow user login
     *
     * @param array $states
     *
     * @return void
     */
    public function setAllowedLoginStates(array $states): void
    {
        $this->allowedLoginStates = $states;
    }

    /**
     * get auth adapters
     *
     * @return array
     */
    public function getAuthAdapters(): array
    {
        return $this->authAdapters;
    }

    /**
     * set auth adapters
     *
     * @param array $authAdapterss
     *
     * @return void
     */
    public function setAuthAdapters(array $authAdapters): void
    {
        $this->authAdapters = $authAdapters;
    }

    /**
     * get auth identity fields
     *
     * @return array
     */
    public function getAuthIdentityFields(): array
    {
        return $this->authIdentityFields;
    }

    /**
     * set auth identity fields
     *
     * @param array $authIdentityFields
     *
     * @return void
     */
    public function setAuthIdentityFields(array $authIdentityFields): void
    {
        $this->authIdentityFields = $authIdentityFields;
    }

    /**
     * get enable username
     *
     * @return bool
     */
    public function getEnableUsername(): bool
    {
        return $this->enableUsername;
    }

    /**
     * set enable username
     *
     * @param bool $flag
     *
     * @return void
     */
    public function setEnableUsername(bool $flag): void
    {
        $this->enableUsername = $flag;
    }

    /**
     * get enable display name
     *
     * @return bool
     */
    public function getEnableDisplayName(): bool
    {
        return $this->enableDisplayName;
    }

    /**
     * set enable display name
     *
     * @param bool $flag
     *
     * @return void
     */
    public function setEnableDisplayName(bool $flag): void
    {
        $this->enableDisplayName = $flag;
    }

    /**
     * get use a captcha in registration form
     *
     * @return bool
     */
    public function getUseRegistrationFormCaptcha(): bool
    {
        return $this->useRegistrationFormCaptcha;
    }

    /**
     * set use a captcha in registration form
     *
     * @param bool $useRegistrationFormCaptcha
     *
     * @return void
     */
    public function setUseRegistrationFormCaptcha(bool $useRegistrationFormCaptcha): void
    {
        $this->useRegistrationFormCaptcha = $useRegistrationFormCaptcha;
    }

    /**
     * get user entity class name
     *
     * @return string
     */
    public function getUserEntityClass(): string
    {
        return $this->userEntityClass;
    }

    /**
     * set user entity class name
     *
     * @param string $userEntityClass
     *
     * @return void
     */
    public function setUserEntityClass(string $userEntityClass): void
    {
        $this->userEntityClass = $userEntityClass;
    }

    /**
     * get password cost
     *
     * @return int
     */
    public function getPasswordCost(): int
    {
        return $this->passwordCost;
    }

    /**
     * set password cost
     *
     * @param int $passwordCost
     *
     * @return void
     */
    public function setPasswordCost(int $passwordCost): void
    {
        $this->passwordCost = $passwordCost;
    }

    /**
     * get user table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * set user table name
     *
     * @param string $tableName
     * @return void
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * get form CAPTCHA options
     *
     * @return array
     */
    public function getFormCaptchaOptions(): array
    {
        return $this->formCaptchaOptions;
    }

    /**
     * set form CAPTCHA options
     *
     * @param array $formCaptchaOptions
     *
     * @return void
     */
    public function setFormCaptchaOptions(array $formCaptchaOptions): void
    {
        $this->formCaptchaOptions = $formCaptchaOptions;
    }
}
