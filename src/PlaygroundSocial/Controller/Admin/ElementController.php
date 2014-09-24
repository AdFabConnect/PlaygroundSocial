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

class ElementController extends AbstractActionController
{
    public function indexAction()
    {
        $config = $this->getServiceLocator()->get('Config');

        $viewModel = new ViewModel();
        return $viewModel->setVariables(array('serviceTypes'  => $config['services']));
    }

    public function createAction()
    {
        $config = $this->getServiceLocator()->get('Config');

        $viewModel = new ViewModel();
        return $viewModel->setVariables(array('serviceTypes'  => $config['services']));
    }
}