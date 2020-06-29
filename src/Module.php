<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Traversable;

/**
 * Class Module
 */
class Module implements ConfigProviderInterface
{
    public const CONTROLLER_NAME = 'mvcuserController';
    public const MVC_USER_AUTH_SERVICE = 'mvcuser_auth_service';
    public const MVC_USER_DB_ADAPTER = 'mvcuser_laminas_db_adapter';
    public const MVC_USER_HYDRATOR = 'mvcuser_user_hydrator';
    public const MVC_USER_FORM_LOGIN = 'mvcuser_login_form';
    public const MVC_USER_FORM_REGISTER = 'mvcuser_register_form';
    public const MVC_USER_FORM_REGISTER_HYDRATOR = 'mvcuser_register_form_hydrator';
    public const MVC_USER_FORM_CHANGE_PASSWORD = 'mvcuser_change_password_form';
    public const MVC_USER_FORM_CHANGE_EMAIL = 'mvcuser_change_email_form';





    public const ROUTE_BASE = 'mvcuser';
    public const ROUTE_CHANGEPASSWD = self::ROUTE_BASE.'/changepassword';
    public const ROUTE_LOGIN = self::ROUTE_BASE.'/login';
    public const ROUTE_LOGOUT = self::ROUTE_BASE.'/logout';
    public const ROUTE_AUTHENTICATE = self::ROUTE_BASE.'/authenticate';
    public const ROUTE_REGISTER = self::ROUTE_BASE.'/register';
    public const ROUTE_CHANGEEMAIL = self::ROUTE_BASE.'/changeemail';

    /**
     * @return array|mixed|Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
