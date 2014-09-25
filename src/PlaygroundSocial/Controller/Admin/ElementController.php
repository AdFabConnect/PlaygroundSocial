<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace PlaygroundSocial\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundSocial\Entity\Element;
use PlaygroundSocial\Entity\Service;

class ElementController extends AbstractActionController
{
    const MAX_PER_PAGE = 20;

    public function indexAction()
    {
        $filters = array();
        $elements = $this->getElementService()->getElementMapper()->findBy($filters, array('id' => 'DESC'));
        $p = $this->getRequest()->getQuery('page', 1);

        $nbElements = count($elements);

        $elementsPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($elements));
        $elementsPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $elementsPaginator->setCurrentPageNumber($p);


        $services = $this->getServiceService()->getServiceMapper()->FindBy(array('active' => 1));
        $viewModel = new ViewModel();

        return $viewModel->setVariables(array('elements'  => $elements,
                                              'services'  => $services,
                                              'elementsPaginator' => $elementsPaginator,
                                              'nbElements' => $nbElements));
    }


    public function moderateAction()
    {
        $elementId = $this->getEvent()->getRouteMatch()->getParam('id');
        $element = $this->getElementService()->getElementMapper()->findById($elementId);

        if($element->getStatus() >= Element::ELEMENT_ACTIVE){
            $element->setStatus(Element::ELEMENT_NOT_ACTIVE);
        } else {
            $element->setStatus(Element::ELEMENT_ACTIVE);
        }

        $this->getElementService()->getElementMapper()->persist($element);

        return $this->redirect()->toRoute('admin/playgroundsocial_social_element');


    }

    public function promoteAction()
    {
        $elementId = $this->getEvent()->getRouteMatch()->getParam('id');
        $element = $this->getElementService()->getElementMapper()->findById($elementId);

        if($element->getService()->getPromote() == Service::SERVICE_ACTIVE) {
            if($element->getStatus() == Element::ELEMENT_PROMOTE){
                $element->setStatus(Element::ELEMENT_ACTIVE);
            } else {
                $element->setStatus(Element::ELEMENT_PROMOTE);
            }
        }

        $this->getElementService()->getElementMapper()->persist($element);

        return $this->redirect()->toRoute('admin/playgroundsocial_social_element');
    }

    public function getElementService()
    {
        return $this->getServiceLocator()->get('playgroundsocial_element_service');    
    }

    public function getServiceService()
    {
        return $this->getServiceLocator()->get('playgroundsocial_service_service');    
    }
}