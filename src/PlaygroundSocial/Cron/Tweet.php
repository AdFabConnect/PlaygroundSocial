<?php
namespace PlaygroundSocial\Cron;

use ZfcBase\EventManager\EventProvider;

class Tweet 
{
    /**
    * @var ServiceLocator $serviceLocator
    */
    protected $serviceLocator;

    public static function import()
    {
        $configuration = require 'config/application.config.php';
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $sm = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig($smConfig));
        $sm->setService('ApplicationConfig', $configuration);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $tweetService = $sm->get('playgroundsocial_twitter_service');
        
        $tweetService->import(); 
 
    }
}