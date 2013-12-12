<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'playgroundweather_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundWeather/Entity'
            ),
            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundWeather\Entity' => 'playgroundweather_entity'
                )
            )
        )
    ),
    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
             __DIR__ . '/../views/admin',
             __DIR__ . '/../views/frontend'
        ),
        'strategies' =>array(
            'ViewJsonStrategy',
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgroundweather'
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'webservice_controller'          => 'PlaygroundWeather\Controller\Frontend\WebServiceController',
            'admin_controller'    => 'PlaygroundWeather\Controller\Admin\AdminController',
            'code_admin_controller'          => 'PlaygroundWeather\Controller\Admin\CodeController',
            'location_admin_controller'      => 'PlaygroundWeather\Controller\Admin\LocationController',
            'imagemap_admin_controller'      => 'PlaygroundWeather\Controller\Admin\ImageMapController',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'weatherTableWidget' => 'PlaygroundWeather\View\Helper\TableWidget',
            'weatherImageWidget' => 'PlaygroundWeather\View\Helper\ImageWidget',
        ),
    ),
    'router' => array(
        'routes' =>array(
            'admin' => array(
                'child_routes' => array(
                    'weather' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/weather',
                            'defaults' => array(
                                'controller' => 'admin_controller',
                                'action' => 'admin',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'codes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/codes',
                                    'defaults' => array(
                                        'controller' => 'code_admin_controller',
                                        'action' => 'associate',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'code_admin_controller',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'code_admin_controller',
                                                'action' => 'associate',
                                            ),
                                        ),
                                    ),
                                    'import' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/import',
                                            'defaults' => array(
                                                'controller' => 'code_admin_controller',
                                                'action' => 'import',
                                            ),
                                        ),
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:codeId',
                                            'constraints' => array(
                                                ':codeId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'code_admin_controller',
                                                'action' => 'remove',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:codeId',
                                            'constraints' => array(
                                                ':codeId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'code_admin_controller',
                                                'action' => 'edit',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                ),
                            ),

                            'locations' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/locations',
                                    'defaults' => array(
                                        'controller' => 'location_admin_controller',
                                        'action' => 'list',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'location_admin_controller',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'location_admin_controller',
                                                'action' => 'list',
                                            ),
                                        ),
                                    ),
                                    'create' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/create/:city/:country[/:region]/:latitude/:longitude',
                                            'constraints' => array(
                                                ':city' => '[a-zA-Z0-9_\-]+',
                                                ':country' => '[a-zA-Z0-9_\-]+',
                                                ':region' => '[a-zA-Z0-9_\-]*',
                                                ':latitude' => '[0-9]{1-3}[0-9]+',
                                                ':longitude' => '[0-9]{1-3}[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'location_admin_controller',
                                                'action' => 'create',
                                            ),
                                        ),
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:locationId',
                                            'constraints' => array(
                                                ':locationId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'location_admin_controller',
                                                'action' => 'remove',
                                            ),
                                        ),
                                    ),
                                ),
                            ),

                            'images' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/images',
                                    'defaults' => array(
                                        'controller' => 'imagemap_admin_controller',
                                        'action' => 'list',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'imagemap_admin_controller',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:imageMapId',
                                            'constraints' => array(
                                                ':imageMapId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'imagemap_admin_controller',
                                                'action' => 'edit',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'imagemap_admin_controller',
                                                'action' => 'list',
                                            ),
                                        ),
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:imageMapId',
                                            'constraints' => array(
                                                ':imageMapId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'imagemap_admin_controller',
                                                'action' => 'remove',
                                            ),
                                        ),
                                    ),
                                ),
                            ),

                        ),
                    ),
                ),
            ),
            'GET' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/GET',
                ),
                'child_routes' => array(
                    'forecast' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/weather/:locationId/:start[/:end]',
                            'constraints' => array(
                                ':locationId' => '[0-9]+',
                                ':start' => '[0-9]{4}\-[0-9]{2}\-[0-9]{2}',
                                ':end' => '[0-9]{4}\-[0-9]{2}\-[0-9]{2}',
                            ),
                            'defaults' => array(
                                'controller' => 'webservice_controller',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'navigation' => array(
        'admin' => array(
            'playgroundweather' => array(
                'label' => 'Weather',
                'route' => 'admin/weather',
                'resource' => 'user',
                'privilege' => 'list',
                'pages' => array(
                    'list-locations' => array(
                        'label' => 'Locations',
                        'route' => 'admin/weather/locations',
                        'resource' => 'weather',
                        'privilege' => 'list',
                    ),
                    'list-codes' => array(
                        'label' => 'Codes',
                        'route' => 'admin/weather/codes',
                        'resource' => 'weather',
                        'privilege' => 'list',
                    ),
                    'list' => array(
                        'label' => 'Image Maps',
                        'route' => 'admin/weather/images',
                        'resource' => 'user',
                        'privilege' => 'list',
                    ),
                ),
            ),
        ),
    ),
);