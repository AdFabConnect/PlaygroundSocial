<?php

namespace PlaygroundSocial\Cron\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundSocial\Cron\Cron as CronController;
use DateTime;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Client\Adapter\Curl;
use Zend\Mail\Message as MailMessage;
use PlaygroundSocial\Entity\Element as InstagramEntity;
use PlaygroundSocial\Entity\Service as ServiceEntity;


class Instagram extends EventProvider implements ServiceManagerAwareInterface
{
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    protected $mailTexte = '';

    protected $maxId = 0;

    public function import()
    {
        $options = "";
        $timeBegin = time();

        $filters = array('type' => 'instagram', 
                         'active' => ServiceEntity::SERVICE_ACTIVE);
        $filters['connectionType'] = ServiceEntity::SERVICE_CONNECTIONTYPE_HASHTAG;
        $services = $this->getServiceManager()->get('playgroundsocial_service_service')->getServiceMapper()->findBy($filters);

        $this->log('Imports instagram '.date('d/m/Y H:i:s'), CronController::SUCCESS);
        $this->mailTexte .= "Imports instagram ".date('d/m/Y H:i:s')." \n";


        
        $nbTweets = 0;
        
        foreach ($services as $service) {
            $this->setService($service);
            $hashtag = $service->getHashtag();

            $lastExecuteds =  $this->getServiceManager()
                                ->get('playgroundsocial_element_service')
                                ->getElementMapper()
                                ->findBy(array('service' => $service), array('socialId', 'DESC'));
            
            if (count($lastExecuteds) > 0) {
                $lastExecuted = $lastExecuteds[0];
                $options = "&min_tag_id=".$lastExecuted->getSocialId();   
            }
            
            $config = $this->getServiceManager()->get('Config');        
            $url = "https://api.instagram.com/v1/tags/".$hashtag."/media/recent?client_id=".$config['instagram_client_id'].$options;
            $nbTweets = $this->constructTwitterUrl($url, $nbTweets);

            if ($nbTweets > 0) {
                $this->log($hashtag.'-'.$service->getId().' : '.$nbTweets.' instagram imported' , CronController::SUCCESS);
            } else {
                $this->log($hashtag.'-'.$service->getId().' : '.$nbTweets.' instagram imported' , CronController::WARN); 
            }

        }
        
        $time = time() - $timeBegin;
        $this->log("Execution time : ".($time).' seconds' , CronController::SUCCESS);

        $this->mailTexte .= "Execution time : ".($time).' seconds'."\n";
    }



    public function constructTwitterUrl($url, $nbTweets)
    {   
        $config = array(
            'adapter'   => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
        );
        $this->log($this->getService()->getHashtag().' new URL : '.$url, CronController::DEBUG);
        
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $return = curl_exec($curl);
        curl_close($curl);

        $tweets = json_decode($return, true);

        $nbTweets = $this->addTweet($tweets, $nbTweets);
        if (!empty($tweets['data']) && !empty($tweets['pagination']['next_url'])) {
            $nbTweets = $this->constructTwitterUrl($tweets['pagination']['next_url'], $nbTweets);
        }
        
        return $nbTweets;
    }


    public function addTweet($tweets, $nbTweets)
    {
        if (!empty($tweets['data'])) {
            foreach ($tweets['data'] as $tweet) {
                $tweetsExist = $this->getTweetMapper()->findBy(array('service'=>$this->getService(),'socialId' => $tweet['id']));


                if (count($tweetsExist) > 0) {
                    $this->log($this->getService()->getHashtag().' - instagram ID : '.$tweet['id'].' don\'t imported : duplicate instagram', CronController::WARN);
                    
                    if ($nbTweets > 0) {
                        $this->log($this->getService()->getHashtag().' : '.$nbTweets.' instagram imported' , CronController::SUCCESS);
                    } else {
                        $this->log($this->getService()->getHashtag().' : '.$nbTweets.' instagram imported' , CronController::WARN); 
                    }

                    exit;

                }
                $dateTime = DateTime::createFromFormat('U', $tweet['created_time']); 
                
                $tweetEntity = new InstagramEntity();
                

                $tweetEntity->setService($this->getService());
                $tweetEntity->setSocialId($tweet['id']);
                $tweetEntity->setAuthor($tweet['user']['username']);
                $tweetEntity->setTimestamp($dateTime);

                if(!empty($tweet['images']['standard_resolution'])){
                    $imageName = $dateTime->format('YmdHis')."_".$tweet['id'].'_'.$tweet['caption']['from']['username'].'.jpg';
                    $image = file_get_contents($tweet['images']['standard_resolution']['url']);
                    if (!is_dir(__DIR__.'/../../../../../../../public/instagram')) {
                        mkdir(__DIR__.'/../../../../../../../public/instagram');
                        chmod(__DIR__.'/../../../../../../../public/instagram', 0777);
                    }
                    $path = __DIR__.'/../../../../../../../public/instagram/'.$imageName;
                    file_put_contents($path, $image);
                    chmod($path, 0755);
                    $tweetEntity->setImage("/instagram/".$imageName);
                    $tweetEntity->setSocialImage($tweet['images']['standard_resolution']['url']);

                }
                $tweetEntity->setText(utf8_encode($tweet['caption']['text']));

                $tweetEntity->setStatus(1);
                if ($this->getService()->getModerationType() == ServiceEntity::SERVICE_MODERATION_PRIORI) {
                    $tweetEntity->setStatus(0);
                }
                //$tweetEntity->setMaxId($tweets['pagination']['next_min_id']);
                $this->getTweetMapper()->insert($tweetEntity);
                $nbTweets ++;
                

            }
        }

        return $nbTweets;
    }
    
    public function getService()
    {
        return $this->service;
    }

    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    public function getTweetMapper()
    {
        return $this->getServiceManager()->get('playgroundsocial_element_mapper');
    }

    public function log($message, $level)
    { 
        CronController::log($message, $level);
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