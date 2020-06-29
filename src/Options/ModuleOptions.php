<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User\Options;

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

    /**
     * @var bool
     */
    protected $useRedirectParameterIfPresent = true;

    /**
     * @var string
     */
    protected $loginRedirectRoute = Module::ROUTE_BASE;

    /**
     * @var string
     */
    protected $logoutRedirectRoute = Module::ROUTE_LOGIN;

    /**
     * @var int
     */
    protected $loginFormTimeout = 300;

    /**
     * @var int
     */
    protected $userFormTimeout = 300;

    /**
     * @var bool
     */
    protected $loginAfterRegistration = true;

    /**
     * @var int
     */
    protected $enableUserState = false;

    /**
     * @var int
     */
    protected $defaultUserState = 1;

    /**
     * @var array
     */
    protected $allowedLoginStates = [null, 1];

    /**
     * @var array
     */
    protected $authAdapters = [100 => DbAdapter::class];

    /**
     * @var array
     */
    protected $authIdentityFields = ['email'];

    /**
     * @var string
     */
    protected $userEntityClass = UserEntity::class;

    /**
     * @var string
     */
    protected $userLoginWidgetViewTemplate = 'mvc-user/user/login.phtml';

    /**
     * @var bool
     */
    protected $enableRegistration = true;

    /**
     * @var bool
     */
    protected $enableUsername = false;

    /**
     * @var bool
     */
    protected $enableDisplayName = false;

    /**
     * @var bool
     */
    protected $useRegistrationFormCaptcha = false;

    /**
     * @var int
     */
    protected $passwordCost = 14;

    /**
     * @var string
     */

    protected $tableName = 'user';

    /**
     * @var array
     */
    protected $formCaptchaOptions
        = [
            'class'   => 'figlet',
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
    public function getLoginRedirectRoute()
    {
        return $this->loginRedirectRoute;
    }

    /**
     * set login redirect route
     *
     * @param string $loginRedirectRoute
     *
     * @return ModuleOptions
     */
    public function setLoginRedirectRoute($loginRedirectRoute)
    {
        $this->loginRedirectRoute = $loginRedirectRoute;
        return $this;
    }

    /**
     * get logout redirect route
     *
     * @return string
     */
    public function getLogoutRedirectRoute()
    {
        return $this->logoutRedirectRoute;
    }

    /**
     * set logout redirect route
     *
     * @param string $logoutRedirectRoute
     *
     * @return ModuleOptions
     */
    public function setLogoutRedirectRoute($logoutRedirectRoute)
    {
        $this->logoutRedirectRoute = $logoutRedirectRoute;
        return $this;
    }

    /**
     * get use redirect param if present
     *
     * @return bool
     */
    public function getUseRedirectParameterIfPresent()
    {
        return $this->useRedirectParameterIfPresent;
    }

    /**
     * set use redirect param if present
     *
     * @param bool $useRedirectParameterIfPresent
     *
     * @return ModuleOptions
     */
    public function setUseRedirectParameterIfPresent($useRedirectParameterIfPresent)
    {
        $this->useRedirectParameterIfPresent = $useRedirectParameterIfPresent;
        return $this;
    }

    /**
     * get the view template for the user login widget
     *
     * @return string
     */
    public function getUserLoginWidgetViewTemplate()
    {
        return $this->userLoginWidgetViewTemplate;
    }

    /**
     * set the view template for the user login widget
     *
     * @param string $userLoginWidgetViewTemplate
     *
     * @return ModuleOptions
     */
    public function setUserLoginWidgetViewTemplate($userLoginWidgetViewTemplate)
    {
        $this->userLoginWidgetViewTemplate = $userLoginWidgetViewTemplate;
        return $this;
    }

    /**
     * get enable user registration
     *
     * @return bool
     */
    public function getEnableRegistration()
    {
        return $this->enableRegistration;
    }

    /**
     * set enable user registration
     *
     * @param bool $enableRegistration
     *
     * @return ModuleOptions
     */
    public function setEnableRegistration($enableRegistration)
    {
        $this->enableRegistration = $enableRegistration;
        return $this;
    }

    /**
     * get login form timeout in seconds
     *
     * @return int
     */
    public function getLoginFormTimeout()
    {
        return $this->loginFormTimeout;
    }

    /**
     * set login form timeout
     *
     * @param int $loginFormTimeout
     *
     * @return ModuleOptions
     */
    public function setLoginFormTimeout($loginFormTimeout)
    {
        $this->loginFormTimeout = $loginFormTimeout;
        return $this;
    }

    /**
     * get user form timeout in seconds
     *
     * @return int
     */
    public function getUserFormTimeout()
    {
        return $this->userFormTimeout;
    }

    /**
     * set user form timeout in seconds
     *
     * @param int $userFormTimeout
     *
     * @return ModuleOptions
     */
    public function setUserFormTimeout($userFormTimeout)
    {
        $this->userFormTimeout = $userFormTimeout;
        return $this;
    }

    /**
     * get login after registration
     *
     * @return bool
     */
    public function getLoginAfterRegistration()
    {
        return $this->loginAfterRegistration;
    }

    /**
     * set login after registration
     *
     * @param bool $loginAfterRegistration
     *
     * @return ModuleOptions
     */
    public function setLoginAfterRegistration($loginAfterRegistration)
    {
        $this->loginAfterRegistration = $loginAfterRegistration;
        return $this;
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
     * @return ModuleOptions
     */
    public function setDefaultUserState($state)
    {
        $this->defaultUserState = $state;
        return $this;
    }

    /**
     * get list of states to allow user login
     *
     * @return array
     */
    public function getAllowedLoginStates()
    {
        return $this->allowedLoginStates;
    }

    /**
     * set list of states to allow user login
     *
     * @param array $states
     *
     * @return ModuleOptions
     */
    public function setAllowedLoginStates(array $states)
    {
        $this->allowedLoginStates = $states;
        return $this;
    }

    /**
     * get auth adapters
     *
     * @return array
     */
    public function getAuthAdapters()
    {
        return $this->authAdapters;
    }

    /**
     * set auth adapters
     *
     * @param array $authAdapterss
     *
     * @return ModuleOptions
     */
    public function setAuthAdapters($authAdapters)
    {
        $this->authAdapters = $authAdapters;
        return $this;
    }

    /**
     * get auth identity fields
     *
     * @return array
     */
    public function getAuthIdentityFields()
    {
        return $this->authIdentityFields;
    }

    /**
     * set auth identity fields
     *
     * @param array $authIdentityFields
     *
     * @return ModuleOptions
     */
    public function setAuthIdentityFields($authIdentityFields)
    {
        $this->authIdentityFields = $authIdentityFields;
        return $this;
    }

    /**
     * get enable username
     *
     * @return bool
     */
    public function getEnableUsername()
    {
        return $this->enableUsername;
    }

    /**
     * set enable username
     *
     * @param bool $flag
     *
     * @return ModuleOptions
     */
    public function setEnableUsername($flag)
    {
        $this->enableUsername = (bool)$flag;
        return $this;
    }

    /**
     * get enable display name
     *
     * @return bool
     */
    public function getEnableDisplayName()
    {
        return $this->enableDisplayName;
    }

    /**
     * set enable display name
     *
     * @param bool $flag
     *
     * @return ModuleOptions
     */
    public function setEnableDisplayName($flag)
    {
        $this->enableDisplayName = (bool)$flag;
        return $this;
    }

    /**
     * get use a captcha in registration form
     *
     * @return bool
     */
    public function getUseRegistrationFormCaptcha()
    {
        return $this->useRegistrationFormCaptcha;
    }

    /**
     * set use a captcha in registration form
     *
     * @param bool $useRegistrationFormCaptcha
     *
     * @return ModuleOptions
     */
    public function setUseRegistrationFormCaptcha($useRegistrationFormCaptcha)
    {
        $this->useRegistrationFormCaptcha = $useRegistrationFormCaptcha;
        return $this;
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
    public function getPasswordCost()
    {
        return $this->passwordCost;
    }

    /**
     * set password cost
     *
     * @param int $passwordCost
     *
     * @return ModuleOptions
     */
    public function setPasswordCost($passwordCost)
    {
        $this->passwordCost = $passwordCost;
        return $this;
    }

    /**
     * get user table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * set user table name
     *
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * get form CAPTCHA options
     *
     * @return array
     */
    public function getFormCaptchaOptions()
    {
        return $this->formCaptchaOptions;
    }

    /**
     * set form CAPTCHA options
     *
     * @param array $formCaptchaOptions
     *
     * @return ModuleOptions
     */
    public function setFormCaptchaOptions($formCaptchaOptions)
    {
        $this->formCaptchaOptions = $formCaptchaOptions;
        return $this;
    }
}
