<?php

declare(strict_types=1);

return [
    'view_manager' => [
        'template_path_stack' => [
            'zfcuser' => __DIR__ . '/../view',
        ],
    ],
    'router' => [
        'routes' => [
            'zfcuser' => [
                'type' => 'Literal',
                'priority' => 1000,
                'options' => [
                    'route' => '/user',
                    'defaults' => [
                        'controller' => 'zfcuser',
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
                                'controller' => 'zfcuser',
                                'action'     => 'login',
                            ],
                        ],
                    ],
                    'authenticate' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/authenticate',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action'     => 'authenticate',
                            ],
                        ],
                    ],
                    'logout' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/logout',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action'     => 'logout',
                            ],
                        ],
                    ],
                    'register' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/register',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action'     => 'register',
                            ],
                        ],
                    ],
                    'changepassword' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/change-password',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action'     => 'changepassword',
                            ],
                        ],
                    ],
                    'changeemail' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/change-email',
                            'defaults' => [
                                'controller' => 'zfcuser',
                                'action' => 'changeemail',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
