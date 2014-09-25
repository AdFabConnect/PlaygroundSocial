<?php
return array(

    'bjyauthorize' => array(
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'social'        => array(),
            ),
        ),
    
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    array(array('admin'), 'social', array('system')),
                ),
            ),
        ),

        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                array('controller' => 'PlaygroundSocial\Controller\ServiceAdmin', 'roles' => array('admin')),
                array('controller' => 'PlaygroundSocial\Controller\ElementAdmin', 'roles' => array('admin')),

                array('controller' => 'playgroundcore_console', 'roles' => array('guest', 'user')),

                array('controller' => 'PlaygroundSocial\Controller\Api\Api', 'roles' => array('guest', 'user')),
                array('controller' => 'PlaygroundSocial\Controller\Api\Element', 'roles' => array('guest', 'user')),

            ),
        ),
    ),
    
    'doctrine' => array(
        'driver' => array(
            'playgroundsocial_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundSocial/Entity'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundSocial\Entity'  => 'playgroundsocial_entity'
                )
            )
        )
    ),

    'service_manager' => array(
        'factories' => array(
            // this definition has to be done here to override Wilmogrod Assetic declaration
            'AsseticBundle\Service' => 'PlaygroundDesign\Assetic\ServiceFactory',
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'nav' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
    ),

    'assetic_configuration' => array(
        'buildOnRequest' => true,
        'debug' => false,
        'acceptableErrors' => array(
            //defaults
            \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND,
            \Zend\Mvc\Application::ERROR_CONTROLLER_INVALID,
            \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH,
            //allow assets when authorisation fails when using the BjyAuthorize module
            \BjyAuthorize\Guard\Route::ERROR,
        ),

        'webPath' => __DIR__ . '/../../../../public',
        'cacheEnabled' => false,
        'cachePath' => __DIR__ . '/../../../../data/cache',
        'modules' => array(
            'lib' => array(
            ),
            'admin' => array(
            ),
            'frontend' => array(                
            ),
        ),
    ),

  'router' => array(
    'routes' => array(

      'admin' => array(
        'child_routes' => array(
          'playgroundsocial_social_service' => array(
            'type' => 'Literal',
            'options' => array(
              'route'    => '/social/services',
              'defaults' => array(
                'controller' => 'PlaygroundSocial\Controller\ServiceAdmin',
                'action'     => 'index',
              ),
            ),
            'may_terminate' => true,
          ),
          'playgroundsocial_social_service_create' => array(
            'type' => 'Literal',
            'options' => array(
              'route'    => '/social/service/create',
              'defaults' => array(
                'controller' => 'PlaygroundSocial\Controller\ServiceAdmin',
                'action'     => 'create',
              ),
            ),
            'may_terminate' => true,
          ),
          'playgroundsocial_social_service_edit' => array(
            'type' => 'Segment',
            'options' => array(
              'route'    => '/social/service/:id/edit',
              'defaults' => array(
                'controller' => 'PlaygroundSocial\Controller\ServiceAdmin',
                'action'     => 'edit',
              ),
              'constraints' => array(
                'id' => '[0-9]*',
            ),
            ),
            'may_terminate' => true,
          ),
          'playgroundsocial_social_service_delete' => array(
            'type' => 'Segment',
            'options' => array(
              'route'    => '/social/service/:id/delete',
              'defaults' => array(
                'controller' => 'PlaygroundSocial\Controller\ServiceAdmin',
                'action'     => 'delete',
              ),
              'constraints' => array(
                 'id' => '[0-9]*',
                ),
            ),
            'may_terminate' => true,
          ),
          'playgroundsocial_social_element' => array(
            'type' => 'Literal',
            'options' => array(
                'route'    => '/social/elements',
                'defaults' => array(
                    'controller' => 'PlaygroundSocial\Controller\ElementAdmin',
                    'action'     => 'index',
                ),
            ),
            'may_terminate' => true,
          ),

          'playgroundsocial_social_service_moderate' => array(
            'type' => 'Segment',
            'options' => array(
              'route'    => '/social/element/:id/moderate',
              'defaults' => array(
                'controller' => 'PlaygroundSocial\Controller\ElementAdmin',
                'action'     => 'moderate',
              ),
              'constraints' => array(
                'id' => '[0-9]*',
            ),
            ),
            'may_terminate' => true,
          ),

          'playgroundsocial_social_service_promote' => array(
            'type' => 'Segment',
            'options' => array(
              'route'    => '/social/element/:id/promote',
              'defaults' => array(
                'controller' => 'PlaygroundSocial\Controller\ElementAdmin',
                'action'     => 'promote',
              ),
              'constraints' => array(
                'id' => '[0-9]*',
            ),
            ),
            'may_terminate' => true,
          ), 
          
          ),
        ),
      ),
        'api' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api',
                    'defaults' => array(
                        'controller' => 'PlaygroundSocial\Controller\Api\Api',
                        'action'     => 'index',
                    ),
                ),
                'child_routes' => array(
                    'list' =>  array(
                       'type' => 'Segment',
                        'options' => array(
                            'route' => '/list',
                            'defaults' => array(
                                'controller' => 'PlaygroundSocial\Controller\Api\Api',
                                'action'     => 'index',
                            ),
                        ),

                    ),
                    'elements' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/elements/service/:service[/limit/:limit][/offset/:offset]',
                            'constraints' => array(
                                'offset' => '[0-9]*',
                                'limit' => '[0-9]*',
                                'service' => '[a-z0-9-_]*',
                            ),
                            'defaults' => array(
                                'controller' => 'PlaygroundSocial\Controller\Api\Element',
                                'action'     => 'list',
                            ),
                        ),
                    ),
                ),
            ),
    ),

    'core_layout' => array(
        'admin' => array(
            'layout' => 'layout/admin',
        ),
        'frontend' => array(
            'layout' => 'layout/layout',
        ),
    ),


    'controllers' => array(
        'invokables' => array(
            'PlaygroundSocial\Controller\ServiceAdmin' => 'PlaygroundSocial\Controller\Admin\ServiceController',
            'PlaygroundSocial\Controller\ElementAdmin' => 'PlaygroundSocial\Controller\Admin\ElementController',

            'PlaygroundSocial\Controller\Api\Api'     => 'PlaygroundSocial\Controller\Api\ApiController',
            'PlaygroundSocial\Controller\Api\Element' => 'PlaygroundSocial\Controller\Api\ElementController',

        ),
    ),
    
    
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'         => 'phpArray',
                'base_dir'     => __DIR__ . '/../language',
                'pattern'      => '%s.php',
                'text_domain'  => 'playgroundsocial'
            ),
        ),
    ),

    'navigation' => array(
        'admin' => array(
             'playgroundsocial' => array(
                'order' => 110,
                'label' => 'Social',
                'route' => 'admin/playgroundsocial_social_service',
                'resource' => 'social',
                'privilege' => 'system',
                'pages' => array(
                    'service' => array(
                        'label' => 'Services',
                        'route' => 'admin/playgroundsocial_social_service',
                        'resource' => 'social',
                        'privilege' => 'system',
                    ),
                    'element' => array(
                        'label' => 'Elements',
                        'route' => 'admin/playgroundsocial_social_element',
                        'resource' => 'social',
                        'privilege' => 'system',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'XHTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_path_stack' => array(
            __DIR__ . '/../view/admin',
            __DIR__ . '/../view/frontend',
        ),
    ),

    "services" => array(
        "instagram" => "Instagram",
        "twitter" => "Twitter",
    ),

    'twitter_config' => array(
        'accessToken' => array(
            'token' => '351504024-AXtku5IjOgaqYiSghPHwxOIfnYyXxAEyX9Tnrf2e',
            'secret' => 'XJMV6rJZHk2qqR1HS4b3FlL97HcaxSGOQy5dk87xqzVML',
        ),
        'oauthOptions' => array(
            'consumerKey' => 'pDv33xvMHNwq1zIvW0M6D5vdE',
            'consumerSecret' => 'GL48jNU6uxI987ME2HDe81SwFFVsBJYsYFNy6RqVcwRqDYb5vy',
        ),
    ),

    'instagram_client_id' => 'c60a4f3433a04b90a21eda643efce6f5',
);
