<?php

declare(strict_types=1);

namespace LaminasFriends\Mvc\User;

use Laminas\Db\Adapter\Adapter;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ControllerPluginProviderInterface;
use Laminas\ModuleManager\Feature\ControllerProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;
use LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter;
use LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapterFactory;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorage;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChain;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainServiceFactory;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorageFactory;
use LaminasFriends\Mvc\User\Factory\AuthenticationService;
use LaminasFriends\Mvc\User\Controller\Plugin\ZfcUserAuthenticationFactory;
use LaminasFriends\Mvc\User\Controller\RedirectCallbackFactory;
use LaminasFriends\Mvc\User\Controller\UserControllerFactory;
use LaminasFriends\Mvc\User\Form\ChangeEmailFormFactory;
use LaminasFriends\Mvc\User\Form\ChangePasswordFormFactory;
use LaminasFriends\Mvc\User\Form\LoginFormFactory;
use LaminasFriends\Mvc\User\Form\RegisterFormFactory;
use LaminasFriends\Mvc\User\Mapper\UserMapperFactory;
use LaminasFriends\Mvc\User\Options\ModuleOptionsFactory;
use LaminasFriends\Mvc\User\Service\UserServiceFactory;
use LaminasFriends\Mvc\User\Factory\UserHydrator;
use LaminasFriends\Mvc\User\View\Helper\ZfcUserDisplayNameFactory;
use LaminasFriends\Mvc\User\View\Helper\ZfcUserIdentityFactory;
use LaminasFriends\Mvc\User\View\Helper\ZfcUserLoginWidgetFactory;

class Module implements
    ControllerProviderInterface,
    ControllerPluginProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{
    public function getConfig($env = null)
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerPluginConfig()
    {
        return [
            'factories' => [
                'zfcUserAuthentication' => ZfcUserAuthenticationFactory::class,
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                'zfcuser' => UserControllerFactory::class,
            ],
        ];
    }

    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                'zfcUserDisplayName' => ZfcUserDisplayNameFactory::class,
                'zfcUserIdentity' => ZfcUserIdentityFactory::class,
                'zfcUserLoginWidget' => ZfcUserLoginWidgetFactory::class,
            ],
        ];

    }

    public function getServiceConfig()
    {
        return [
            'aliases' => [
                'zfcuser_zend_db_adapter' => Adapter::class,
            ],
            'invokables' => [
                'zfcuser_register_form_hydrator' => ClassMethodsHydrator::class,
            ],
            'factories' => [
                'zfcuser_redirect_callback' => RedirectCallbackFactory::class,
                'zfcuser_module_options'    => ModuleOptionsFactory::class,
                AdapterChain::class         => AdapterChainServiceFactory::class,

                // We alias this one because it's ZfcUser's instance of
                // Laminas\Authentication\AuthenticationService. We don't want to
                // hog the FQCN service alias for a Laminas\* class.
                'zfcuser_auth_service'      => AuthenticationService::class,

                'zfcuser_user_hydrator' => UserHydrator::class,
                'zfcuser_user_mapper' => UserMapperFactory::class,

                'zfcuser_login_form' => LoginFormFactory::class,
                'zfcuser_register_form' => RegisterFormFactory::class,
                'zfcuser_change_password_form' => ChangePasswordFormFactory::class,
                'zfcuser_change_email_form' => ChangeEmailFormFactory::class,

                DbAdapter::class => DbAdapterFactory::class,
                DbStorage::class => DbStorageFactory::class,

                'zfcuser_user_service' => UserServiceFactory::class,
            ],
        ];
    }
}
