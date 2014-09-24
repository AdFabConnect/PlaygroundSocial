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

class ServiceController extends AbstractActionController
{
    public function indexAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        $services = $this->getServiceService()->getServiceMapper()->findAll();

        $viewModel = new ViewModel();
        return $viewModel->setVariables(array('serviceTypes' => $config["services"], 'services' => $services));
    }

    public function createAction()
    {
        $form = $this->getServiceForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );  
            $service = $this->getServiceService()->create($data);
            if ($service) {
                $this->flashMessenger()->addMessage(' alert-success');
                $this->flashMessenger()->addMessage('The service "'.$service->getType().'" was created');

                return $this->redirect()->toRoute('admin/playgroundsocial_social_service');
            } else {
                $state = 'alert-danger';
                $message = 'The service was not created!';
            }
            
        }

        $viewModel = new ViewModel();
        return $viewModel->setVariables(array("form" => $form, 'service' => ''));

    }

    public function editAction()
    {
        $serviceId = $this->getEvent()->getRouteMatch()->getParam('id');
        $service = $this->getServiceService()->getServiceMapper()->findById($serviceId);
        $form = $this->getServiceForm();
        $request = $this->getRequest();
        

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );  
            $service = $this->getServiceService()->edit($service, $data);
            if ($service) {
                $this->flashMessenger()->addMessage(' alert-success');
                $this->flashMessenger()->addMessage('The service "'.$service->getType().'" was created');

                return $this->redirect()->toRoute('admin/playgroundsocial_social_service');
            } else {
                $state = 'alert-danger';
                $message = 'The service was not created!';
            }
        }

        $form->bind($service);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-social/service/create');
        return $viewModel->setVariables(array("form" => $form, 'service' => $service));
    }

    public function deleteAction()
    {

        $serviceId = $this->getEvent()->getRouteMatch()->getParam('id');
        $service = $this->getServiceService()->getServiceMapper()->findById($serviceId);

        $this->getServiceService()->getServiceMapper()->remove($service);

        return $this->redirect()->toRoute('admin/playgroundsocial_social_service');
    }

    public function getServiceForm()
    {
        return $this->getServiceLocator()->get('playgroundsocial_service_form'); 
    }

    public function getServiceService()
    {
        return $this->getServiceLocator()->get('playgroundsocial_service_service');    
    }
}