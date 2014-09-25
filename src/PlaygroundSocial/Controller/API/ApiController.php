<?php
namespace PlaygroundSocial\Controller\Api;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Response;

class ApiController extends AbstractActionController
{
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
    public function indexAction()
    {
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $routes = array();

        $routes['element'][] = array(
            'routes' => '/api/elements/service/:service',
            'message' => 'retourne des elements pour les services',
            'required params' => array(
                'service' => '[a-z]*',
            ),
            'optionnals params' => array(
                'limit' => '[0-9]*',
                'offset' => '[0-9]*',
            ),
            'examples' => array(
                'api/elements/service/twitter-adfab',
                'api/elements/service/twitter-adfab/limit/10',
                'api/elements/service/twitter-adfab/offset/10',
                'api/elements/service/twitter-adfab/imit/10/offset/10'
            )
        );

        $return['status'] = 0;
        $return['routes'] = $routes;

        $response->setContent(json_encode($return));
        
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Cache-Control', 'max-age=60')
                               ->addHeaderLine('User-Cache-Control', 'max-age=60')
                               ->addHeaderLine('Expires', gmdate("D, d M Y H:i:s", time() + 60))
                               ->addHeaderLine('Pragma', 'cache');
        
        return $response;
    }
}