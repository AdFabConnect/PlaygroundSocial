<?php

namespace PlaygroundSocial\Cron\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundSocial\Cron\Cron as CronController;
use DateTime;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Mail\Message as MailMessage;
use PlaygroundSocial\Entity\Element as TweetEntity;
use PlaygroundSocial\Entity\Service as ServiceEntity;


class Twitter extends EventProvider implements ServiceManagerAwareInterface
{
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    protected $service;

    protected $mailTexte = '';

    protected $maxId = 0;

    public function import()
    {
        $timeBegin = time();

        $filters = array('type' => 'twitter', 
                         'active' => ServiceEntity::SERVICE_ACTIVE);
        $filters['connectionType'] = ServiceEntity::SERVICE_CONNECTIONTYPE_HASHTAG;
        $services = $this->getServiceManager()->get('playgroundsocial_service_service')->getServiceMapper()->findBy($filters);


        $this->log('Imports Tweets '.date('d/m/Y H:i:s'), CronController::SUCCESS);
        $this->mailTexte .= "Imports Tweets ".date('d/m/Y H:i:s')." \n";


        foreach ($services as $service) {
            $this->setService($service);
            $hashtag = $service->getHashtag();

            $nbTweets = 0;
            $options = array('lang' => 'fr');   
            
            $twitter = $this->getTwitterClient();

            $lastExecuteds =  $this->getServiceManager()
                                ->get('playgroundsocial_element_service')
                                ->getElementMapper()
                                ->findBy(array('service' => $service), array('socialId' => 'DESC'));
            
            if (count($lastExecuteds) > 0) {
                $lastExecuted = $lastExecuteds[0];
                $options = array('since_id' => $lastExecuted->getSocialId());   
            }else{
                $options = array('since_id' => 0);   
            }
            
            $nbTweets = $this->constructTwitterUrl($twitter, $hashtag, $options, $nbTweets);

            if ($nbTweets > 0) {
                $this->log($hashtag.'-'.$service->getId().' : '.$nbTweets.' tweets imported' , CronController::SUCCESS);
            } else {
                $this->log($hashtag.'-'.$service->getId(). ': '.$nbTweets.' tweet imported' , CronController::WARN); 
            }
        }

        $time = time() - $timeBegin;
        $this->log("Execution time : ".($time).' seconds' , CronController::SUCCESS);

        $this->mailTexte .= "Execution time : ".($time).' seconds'."\n";
    }


    public function getTwitterClient()
    {
        $config = $this->getServiceManager()->get('Config');
        $twitter = new \ZendService\Twitter\Twitter($config['twitter_config']);

        $twitter->getHttpClient()->setAdapter('Zend\Http\Client\Adapter\Curl');

        return $twitter;
    }


    public function constructTwitterUrl($twitter, $keyword, $options, $nbTweets)
    {

        $response = $twitter->search->tweets($keyword, $options);
        $tweets = json_decode($response->getRawResponse(), true);

        $nbTweets = $this->addTweet($tweets, $nbTweets, $keyword);
        if (!empty($tweets['statuses']) && !empty($tweets['search_metadata']['next_results'])) {
            $this->log($keyword.' new URL : '.$tweets['search_metadata']['next_results'], CronController::DEBUG);
            $urlArray = explode("&", trim($tweets['search_metadata']['next_results'], '?'));
            foreach ($urlArray as $urlArray) {
                $paramsArray = explode("=", $urlArray);
                $options[$paramsArray[0]] = $paramsArray[1];
            }
            $nbTweets = $this->constructTwitterUrl($twitter, $keyword, $options, $nbTweets);
        }
        

        return $nbTweets;
    }


    public function addTweet($tweets, $nbTweets, $keyword)
    {
        if (!empty($tweets['statuses'])) {
            foreach ($tweets['statuses'] as $tweet) {
                $tweetsExist = $this->getTweetMapper()->findBy(array('service'=>$this->getService(),'socialId' => $tweet['id_str']));
                if (count($tweetsExist) > 0) {
                    $this->log($keyword.' - Tweet ID : '.$tweet['id_str'].' don\'t imported : duplicate tweet', CronController::WARN);
                    continue;
                }
                
                /*$localeAvailable = array();
                $service = $this->getService();
                foreach ($service->getLocales() as $locale) {
                    $locale = explode('_', $locale->getLocale());
                    $localeAvailable[$locale[0]] = $locale[0];
                }

                if (!in_array($tweet['lang'], $localeAvailable)) {
                    return $nbTweets;
                }*/

                $dateTime = new DateTime($tweet['created_at']);
                
                $tweetEntity = new TweetEntity();
              
                $tweetEntity->setService($this->getService());
                $tweetEntity->setSocialId($tweet['id_str']);
                $tweetEntity->setAuthor($tweet['user']['screen_name']);

                $tweetEntity->setTimestamp($dateTime);
                if(!empty($tweet['entities']['media'])){
                    $imageName = $dateTime->format('YmdHis')."_".$tweet['id_str'].'_'.$tweet['user']['screen_name'].'.jpg';
                    $image = file_get_contents($tweet['entities']['media'][0]['media_url']);
                    if (!is_dir(__DIR__.'/../../../../../../../public/twitter')) {
                        mkdir(__DIR__.'/../../../../../../../public/twitter');
                        chmod(__DIR__.'/../../../../../../../public/twitter', 0777);
                    }
                    $path = __DIR__.'/../../../../../../../public/twitter/'.$imageName;
                    file_put_contents($path, $image);
                    chmod($path, 0777);
                    $tweetEntity->setImage("/twitter/".$imageName);
                    $tweetEntity->setSocialImage($tweet['entities']['media'][0]['media_url']);
                }

                $tweetEntity->setText(utf8_encode($tweet['text']));
                $tweetEntity->setStatus(1);
                if ($this->getService()->getModerationType() == ServiceEntity::SERVICE_MODERATION_PRIORI) {
                    $tweetEntity->setStatus(0);
                }
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