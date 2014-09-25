<?php
namespace PlaygroundSocial\Controller\Api;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Response;

class ElementController extends AbstractActionController
{
    const LIMIT = 20;
    /**
    * @var ServiceLocator $serviceLocator
    */
    protected $serviceLocator;


    /**
    * Permet de récuperer la liste des comment
    *  
    * key : clé de traduction à chercher
    * locale : locale de traduction
    *
    * Retour un tableau JSON des traductions
    *
    * @return Reponse $response
    */
    public function listAction()
    {
        $return = array();
        $response = $this->getResponse();
        $response->setStatusCode(200);

        $service = strtolower($this->getEvent()->getRouteMatch()->getParam('service'));
        $offset = strtolower($this->getEvent()->getRouteMatch()->getParam('offset'));
        $limit = strtolower($this->getEvent()->getRouteMatch()->getParam('limit'));

       

        if (empty($service)) {
            $return['status'] = 1;
            $return['message'] = "invalid argument : service is required";
            $response->setContent(json_encode($return));
        
            return $response;
        }

        $services = $this->getServiceLocator()->get('playgroundsocial_service_service')->getServiceMapper()->findBy(array('slug' => $service));
        if (count($services) == 0) {
            $return['status'] = 1;
            $return['message'] = "invalid argument : service is not verified";
            $response->setContent(json_encode($return));
        
            return $response;
        }

        $service = $services[0];



        if (empty($offset)) {
            $offset = 0;
        }

        if(empty($limit) || $limit > 100) {
            $limit = self::LIMIT;
        }


        $elements = $this->getServiceLocator()->get('playgroundsocial_element_service')->getElementMapper()->findBy(
            array("service" => $service->getId(), 'status' => array(1)),
            array('id' => 'DESC'),
            $limit,
            $offset * $limit
            );

        $elementsTab = array();

        foreach ($elements as $element) {
            $elementsTab[] = array(
                'id'        => $element->getId(),
                'timestamp' => $element->getTimestamp()->getTimestamp(),
                'pseudo'    => $element->getAuthor(),
                'content'   => utf8_decode($element->getText()),
                'image'     => utf8_decode($element->getImage()),
                'service'   => $service->getName(),
                'visible'   => $element->getStatus());
        }

        $return['status'] = 0;
        $return['nb_results'] = count($elementsTab);
        $return['comments'] = $elementsTab;



        if($offset > 0) {
            $return['pagination']['previous'] = '/api/elements/service/twitter-tf1/limit/'.$limit.'/offset/'.($offset-1);     
        }

        $return['pagination']['next'] = '/api/elements/service/twitter-tf1/limit/'.$limit.'/offset/'.($offset+1);


        $response->setContent(json_encode($return));

        $config = $this->getServiceLocator()->get('Config');

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Cache-Control', 'max-age=60')
                               ->addHeaderLine('User-Cache-Control', 'max-age=60')
                               ->addHeaderLine('Expires', gmdate("D, d M Y H:i:s", time() + 60))
                               ->addHeaderLine('Pragma', 'cache');

        return $response;
    }
}