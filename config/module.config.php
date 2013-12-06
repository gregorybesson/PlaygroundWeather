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
            'playgroundweather_controller'          => 'PlaygroundWeather\Controller\Frontend\PlaygroundWeatherController',
            'weatheroccurrence_controller'          => 'PlaygroundWeather\Controller\Frontend\WeatherOccurrenceController',
            'playgroundweather_admin_controller'    => 'PlaygroundWeather\Controller\Admin\AdminController',
            'weathercode_admin_controller'          => 'PlaygroundWeather\Controller\Admin\WeatherCodeController',
            'weatherlocation_admin_controller'      => 'PlaygroundWeather\Controller\Admin\WeatherLocationController',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'weatherTableWidget' => 'PlaygroundWeather\View\Helper\WeatherTableWidget',
            'weatherMapWidget' => 'PlaygroundWeather\View\Helper\WeatherMapWidget',
        ),
    ),
    'router' => array(
        'routes' =>array(
            'frontend' => array(
                'child_routes' => array(
                    'meteo' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'meteo',
                            'defaults' => array(
                                'controller' => 'playgroundweather_controller',
                                'action' => 'index',
                            )
                        ),
                        'may_terminate' => true,
                    ),
                ),
            ),
            'admin' => array(
                'child_routes' => array(
                    'meteo' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/meteo',
                            'defaults' => array(
                                'controller' => 'playgroundweather_admin_controller',
                                'action' => 'admin',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'weather-codes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/weather-codes',
                                    'defaults' => array(
                                        'controller' => 'weathercode_admin_controller',
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
                                                'controller' => 'weathercode_admin_controller',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'weathercode_admin_controller',
                                                'action' => 'associate',
                                            ),
                                        ),
                                    ),
                                    'import' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/import',
                                            'defaults' => array(
                                                'controller' => 'weathercode_admin_controller',
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
                                                'controller' => 'weathercode_admin_controller',
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
                                                'controller' => 'weathercode_admin_controller',
                                                'action' => 'edit',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                ),
                            ),

                            'weather-locations' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/weather-locations',
                                    'defaults' => array(
                                        'controller' => 'weatherlocation_admin_controller',
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
                                                'controller' => 'weatherlocation_admin_controller',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'weatherlocation_admin_controller',
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
                                                'controller' => 'weatherlocation_admin_controller',
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
                                                'controller' => 'weatherlocation_admin_controller',
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
                                'controller' => 'weatheroccurrence_controller',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);