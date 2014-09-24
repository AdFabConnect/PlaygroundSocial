<?php

namespace PlaygroundSocial\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use PlaygroundGallery\Entity\Media as MediaEntity;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use PlaygroundSocial\Entity\Service as ServiceEntity;

class Service extends ProvidesEventsForm
{

    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {

        parent::__construct($name);
        $this->setServiceManager($sm);
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'type',
            'options' => array(
                'label' => $translator->translate('Choose a service', 'playgroundsocial'),
                'value_options' => $this->getServiceType(),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'connectionType',
            'options' => array(
                'label' => $translator->translate('Choose a type of connection :', 'playgroundsocial'),
                'value_options' => $this->getConnectionType(),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'name' => 'username',
            'options' => array(
                'label' => $translator->translate('Username', 'playgroundsocial'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Username', 'playgroundsocial'),
                'class' => 'form-control'
            ),
            'validator' => array(
                new \Zend\Validator\NotEmpty(),
            )
        ));
        
        
        $this->add(array(
            'name' => 'password',
            'options' => array(
                'label' => $translator->translate('Password', 'playgroundsocial'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Password', 'playgroundsocial'),
                'class' => 'form-control'
            ),
            'validator' => array(
                new \Zend\Validator\NotEmpty(),
            )
        ));

        $this->add(array(
            'name' => 'hashtag',
            'options' => array(
                'label' => $translator->translate('Hashtag', 'playgroundsocial'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('#Hashtag', 'playgroundsocial'),
                'class' => 'form-control'
            ),
            'validator' => array(
                new \Zend\Validator\NotEmpty(),
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'moderationType',
            'options' => array(
                'label' => $translator->translate('Choose a type of moderation', 'playgroundsocial'),
                'value_options' => $this->getModerationType(),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'promote',
            'options' => array(
                'label' => $translator->translate('Promote', 'playgroundsocial'),
                'value_options' => $this->getPromote(),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'active',
            'options' => array(
                'label' => $translator->translate('Active', 'playgroundsocial'),
                'value_options' => $this->getActive(),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'locales',
            'options' => array(
                'label' => $translator->translate('Locale', 'playgroundsocial'),
                'value_options' => $this->getLocales(),
            ),
            'attributes' => array(
                'class' => 'form-control multiselect',
                'multiple' => 'multiple',
                'value' => array_keys($this->getLocales()),
             ),
         ));

        
        $submitElement = new Element\Button('submit');
        $submitElement->setLabel($translator->translate('Validate', 'playgroundsocial'))
            ->setAttributes(array(
            'type' => 'submit',
            'class'=> 'btn btn-success'
        ));

        $this->add($submitElement, array(
            //'priority' => - 100
        ));

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


    public function getServiceType()
    {
        $config = $this->getServiceManager()->get('Config');

        return $config["services"];
    }

    public function getConnectionType()
    {
        return ServiceEntity::$connectionTypes;
    }

    public function getModerationType()
    {
        return ServiceEntity::$moderationTypes;
    }

    public function getPromote()
    {
        return ServiceEntity::$statuses;
    }

    public function getActive()
    {
        return ServiceEntity::$statuses;
    }

    public function getLocales()
    {
        $locales = $this->getServiceManager()->get('playgroundcore_locale_service')->getLocaleMapper()->findAll();
        $localesForm = array();
        foreach ($locales as $locale) {
            $localesForm[$locale->getId()] = $locale->getName();
        }

        return $localesForm;
    }


}
