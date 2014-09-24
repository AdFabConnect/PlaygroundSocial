<?php

namespace PlaygroundSocial;

use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    protected $eventsArray = array();
    
    public function onBootstrap(MvcEvent $e)
    {
        $application     = $e->getTarget();
        $serviceManager  = $application->getServiceManager();
        $eventManager    = $application->getEventManager();

        $translator = $serviceManager->get('translator');
       
        // Gestion de la locale
        if (PHP_SAPI !== 'cli') {
            $locale = null;
            $options = $serviceManager->get('playgroundcore_module_options');
            
            $locale = $options->getLocale();

            $translator->setLocale($locale);
        
            // plugins
            $translate = $serviceManager->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);  
        }

        $eventManager->getSharedManager()->attach('Zend\Mvc\Application','getCronjobs', array($this, 'addCronjob'));
        
        AbstractValidator::setDefaultTranslator($translator,'playgroundsocial');
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'playgroundsocial_doctrine_em' => 'doctrine.entitymanager.orm_default',
            ),
            'factories' => array(
                'playgroundsocial_module_options' => function  ($sm) {
                    $config = $sm->get('Configuration');
                    
                    return new Options\ModuleOptions(isset($config['playgroundsocial']) ? $config['playgroundsocial'] : array());
                },   

                'playgroundsocial_service_form'  => function  ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Service(null, $sm, $translator);
                    
                    return $form;                
                },

                 'playgroundsocial_service_mapper' => function  ($sm) {
                    return new Mapper\Service($sm->get('playgroundsocial_doctrine_em'), $sm->get('playgroundsocial_module_options'));
                },

                'playgroundsocial_element_mapper' => function  ($sm) {
                    return new Mapper\Element($sm->get('playgroundsocial_doctrine_em'), $sm->get('playgroundsocial_module_options'));
                },
            ),
            'invokables' => array(
                'playgroundsocial_service_service'   => 'PlaygroundSocial\Service\Service',
                'playgroundsocial_element_service'   => 'PlaygroundSocial\Service\Element',
                
                'playgroundsocial_twitter_service'   => 'PlaygroundSocial\Cron\Service\Twitter',
                'playgroundsocial_instagram_service'   => 'PlaygroundSocial\Cron\Service\Instagram',


            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
            )
        );
    }

    public function addCronjob($e)
    {
        $cronjobs = $e->getParam('cronjobs');
        
        $cronjobs['tweets_imports'] = array(
            'frequency' => '*/5 * * * *',
            'callback'  => '\PlaygroundSocial\Cron\Tweet::import',
            'args'      => array(),
        );
        
        $cronjobs['instagram_imports'] = array(
            'frequency' => '*/5 * * * *',
            'callback'  => '\PlaygroundSocial\Cron\Instagram::import',
            'args'      => array(),
        );

        return $cronjobs;
    }
}
