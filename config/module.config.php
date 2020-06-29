<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use Laminas\Hydrator\ClassMethodsHydrator;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainService;
use LaminasFriends\Mvc\User\Authentication\Adapter\AdapterChainServiceFactory;
use LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapter;
use LaminasFriends\Mvc\User\Authentication\Adapter\DbAdapterFactory;
use LaminasFriends\Mvc\User\Authentication\Service\AuthenticationServiceFactory;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorage;
use LaminasFriends\Mvc\User\Authentication\Storage\DbStorageFactory;
use LaminasFriends\Mvc\User\Controller\Plugin\UserAuthenticationPlugin;
use LaminasFriends\Mvc\User\Controller\Plugin\UserAuthenticationPluginFactory;
use LaminasFriends\Mvc\User\Controller\RedirectCallback;
use LaminasFriends\Mvc\User\Controller\RedirectCallbackFactory;
use LaminasFriends\Mvc\User\Controller\UserControllerFactory;
use LaminasFriends\Mvc\User\Form\ChangeEmailFormFactory;
use LaminasFriends\Mvc\User\Form\ChangePasswordFormFactory;
use LaminasFriends\Mvc\User\Form\LoginFormFactory;
use LaminasFriends\Mvc\User\Form\RegisterFormFactory;
use LaminasFriends\Mvc\User\Mapper\UserHydratorFactory;
use LaminasFriends\Mvc\User\Mapper\UserMapper;
use LaminasFriends\Mvc\User\Mapper\UserMapperFactory;
use LaminasFriends\Mvc\User\Module;
use LaminasFriends\Mvc\User\Options\ModuleOptions;
use LaminasFriends\Mvc\User\Options\ModuleOptionsFactory;
use LaminasFriends\Mvc\User\Service\UserService;
use LaminasFriends\Mvc\User\Service\UserServiceFactory;
use LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayName;
use LaminasFriends\Mvc\User\View\Helper\MvcUserDisplayNameFactory;
use LaminasFriends\Mvc\User\View\Helper\MvcUserIdentity;
use LaminasFriends\Mvc\User\View\Helper\MvcUserIdentityFactory;
use LaminasFriends\Mvc\User\View\Helper\MvcUserLoginWidget;
use LaminasFriends\Mvc\User\View\Helper\MvcUserLoginWidgetFactory;

return [
    'controllers' => [
        'factories' => [
            Module::CONTROLLER_NAME => UserControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'aliases' => [
            'mvcUserAuthentication' =>  UserAuthenticationPlugin::class
        ],
        'factories' => [
            UserAuthenticationPlugin::class => UserAuthenticationPluginFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Module::MVC_USER_DB_ADAPTER => Adapter::class,
        ],
        'invokables' => [
            Module::MVC_USER_FORM_REGISTER_HYDRATOR => ClassMethodsHydrator::class,
        ],
        'factories' => [
            RedirectCallback::class => RedirectCallbackFactory::class,
            ModuleOptions::class         => ModuleOptionsFactory::class,
            AdapterChainService::class   => AdapterChainServiceFactory::class,

            // We alias this one because it's ZfcUser's instance of
            // Laminas\Authentication\AuthenticationServiceFactory. We don't want to
            // hog the FQCN service alias for a Laminas\* class.
            Module::MVC_USER_AUTH_SERVICE => AuthenticationServiceFactory::class,

            Module::MVC_USER_HYDRATOR => UserHydratorFactory::class,
            UserMapper::class => UserMapperFactory::class,

            Module::MVC_USER_FORM_LOGIN => LoginFormFactory::class,
            Module::MVC_USER_FORM_REGISTER => RegisterFormFactory::class,
            Module::MVC_USER_FORM_CHANGE_PASSWORD => ChangePasswordFormFactory::class,
            Module::MVC_USER_FORM_CHANGE_EMAIL => ChangeEmailFormFactory::class,

            DbAdapter::class => DbAdapterFactory::class,
            DbStorage::class => DbStorageFactory::class,
            UserService::class => UserServiceFactory::class,//zfcuser_user_service
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'mvcUserDisplayName' => MvcUserDisplayName::class,
            'mvcUserIdentity' => MvcUserIdentity::class,
            'mvcUserLoginWidget' => MvcUserLoginWidget::class,
        ],
        'factories' => [
            MvcUserDisplayName::class => MvcUserDisplayNameFactory::class,
            MvcUserIdentity::class => MvcUserIdentityFactory::class,
            MvcUserLoginWidget::class => MvcUserLoginWidgetFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'mvcuser' => __DIR__ . '/../view',
        ],
    ],
    'router' => [
        'routes' => [
            Module::ROUTE_BASE => [
                'type' => 'Literal',
                'priority' => 1000,
                'options' => [
                    'route' => '/user',
                    'defaults' => [
                        'controller' => Module::CONTROLLER_NAME,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'login' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/login',
                            'defaults' => [
                                'controller' => Module::CONTROLLER_NAME,
                                'action'     => 'login',
                            ],
                        ],
                    ],
                    'authenticate' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/authenticate',
                            'defaults' => [
                                'controller' => Module::CONTROLLER_NAME,
                                'action'     => 'authenticate',
                            ],
                        ],
                    ],
                    'logout' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/logout',
                            'defaults' => [
                                'controller' => Module::CONTROLLER_NAME,
                                'action'     => 'logout',
                            ],
                        ],
                    ],
                    'register' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/register',
                            'defaults' => [
                                'controller' => Module::CONTROLLER_NAME,
                                'action'     => 'register',
                            ],
                        ],
                    ],
                    'changepassword' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/change-password',
                            'defaults' => [
                                'controller' => Module::CONTROLLER_NAME,
                                'action'     => 'changepassword',
                            ],
                        ],
                    ],
                    'changeemail' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/change-email',
                            'defaults' => [
                                'controller' => Module::CONTROLLER_NAME,
                                'action' => 'changeemail',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
