<?php

namespace PlaygroundSocial\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundSocial\Entity\Service as ServiceEntity;

class Service extends EventProvider implements ServiceManagerAwareInterface
{

    /**
    * @var missionMapper
    */
    protected $serviceMapper;

    public function create(array $data)
    {

        if($data['connectionType'] == ServiceEntity::SERVICE_CONNECTIONTYPE_ACCOUNT){
            if(empty($data['username']) || empty($data['password'])) {
                
                return false;
            }
        }

        if($data['connectionType'] == ServiceEntity::SERVICE_CONNECTIONTYPE_HASHTAG){
            if(empty($data['hashtag'])) {
                
                return false;
            }
        }

        $service = new ServiceEntity();
        $service->setType($data['type']);
        $service = $service->setName($data['name']);
        $service->setConnectionType($data['connectionType']);
        $service->setUsername($data['username']);
        $service->setPassword($data['password']);
        $service->setHashtag($data['hashtag']);
        $service->setModerationType($data['moderationType']);
        $service->setPromote($data['promote']);
        $service->setActive($data['active']);

        $service = $this->getServiceMapper()->persist($service);

        foreach ($data["locales"] as $locale) {
            $locale = $this->getServiceManager()->get('playgroundcore_locale_mapper')->findById($locale);
            $service->addLocale($locale);
        }

        $service = $this->getServiceMapper()->persist($service);
        
        return $service;
    }

    public function edit($service, $data){

        if($data['connectionType'] == ServiceEntity::SERVICE_CONNECTIONTYPE_ACCOUNT){
            if(empty($data['username']) || empty($data['password'])) {
                
                return false;
            }
        }

        if($data['connectionType'] == ServiceEntity::SERVICE_CONNECTIONTYPE_HASHTAG){
            if(empty($data['hashtag'])) {
                
                return false;
            }
        }

        $service->setType($data['type']);
        $service = $service->setName($data['name']);
        $service->setConnectionType($data['connectionType']);
        $service->setUsername($data['username']);
        $service->setPassword($data['password']);
        $service->setHashtag($data['hashtag']);
        $service->setModerationType($data['moderationType']);
        $service->setPromote($data['promote']);
        $service->setActive($data['active']);
        $service->removeLocale();
        $service = $this->getServiceMapper()->persist($service);

        foreach ($data["locales"] as $locale) {
            $locale = $this->getServiceManager()->get('playgroundcore_locale_mapper')->findById($locale);
            $service->addLocale($locale);
        }

        $service = $this->getServiceMapper()->persist($service);
        
        return $service;

    }

    public function getServiceMapper()
    {
        if (null === $this->serviceMapper) {
            $this->serviceMapper = $this->getServiceManager()->get('playgroundsocial_service_mapper');
        }

        return $this->serviceMapper;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}