<?php

namespace PlaygroundSocial\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;

class Element extends EventProvider implements ServiceManagerAwareInterface
{

    /**
    * @var missionMapper
    */
    protected $serviceMapper;


    public function getElementMapper()
    {
        if (null === $this->serviceMapper) {
            $this->serviceMapper = $this->getServiceManager()->get('playgroundsocial_element_mapper');
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