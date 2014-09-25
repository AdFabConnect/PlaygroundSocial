<?php

namespace PlaygroundSocial\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PlaygroundCore\Filter\Slugify;



/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="social_service")
 */
class Service implements InputFilterAwareInterface
{

    const SERVICE_CONNECTIONTYPE_ACCOUNT = 1;
    const SERVICE_CONNECTIONTYPE_HASHTAG = 2;

    public static $connectionTypes = array(self::SERVICE_CONNECTIONTYPE_ACCOUNT => "Acccount",
                                           self::SERVICE_CONNECTIONTYPE_HASHTAG => "Hashtag");
    
    const SERVICE_MODERATION_LIVE = 1;
    const SERVICE_MODERATION_PRIORI = 2;
    const SERVICE_MODERATION_POSTPRIORI = 3;

    public static $moderationTypes = array(self::SERVICE_MODERATION_LIVE => "In realtime",
                                           self::SERVICE_MODERATION_PRIORI => "Priori",
                                           self::SERVICE_MODERATION_POSTPRIORI => "Posteriori");

    
    const SERVICE_ACTIVE = 1;
    const SERVICE_NOT_ACTIVE = 0;

    public static $statuses = array(self::SERVICE_NOT_ACTIVE => "No",
                                    self::SERVICE_ACTIVE => "Yes");

    protected $inputFilter;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $type;

     /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $slug;

    /**
     * @ORM\Column(name="connection_type", type="string", nullable=false)
     */
    protected $connectionType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $hashtag;

    /**
     * @ORM\Column(name="moderation_type",type="string", nullable=false)
     */
    protected $moderationType;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $active;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $promote;

     /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="PlaygroundCore\Entity\Locale")
     * @ORM\JoinTable(name="social_service_locale",
     *      joinColumns={@ORM\JoinColumn(name="service_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="locale_id", referencedColumnName="id")}
     * )
     */
    protected $locales;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;


    public function __construct()
    {
        $this->locales = new ArrayCollection();
    }

    /**
     * @param string $id
     * @return Category
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

     public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        $slugify = new Slugify;
        $this->setSlug($slugify->filter($name));

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getConnectionType()
    {
        return $this->connectionType;
    }

    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getHashtag()
    {
        return $this->hashtag;
    }

    public function setHashtag($hashtag)
    {
        $this->hashtag = $hashtag;
        return $this;
    }

    public function getModerationType()
    {
        return $this->moderationType;
    }

    public function setModerationType($moderationType)
    {
        $this->moderationType = $moderationType;
        return $this;
    }

    public function getPromote()
    {
        return $this->promote;
    }

    public function setPromote($promote)
    {
        $this->promote = $promote;
        return $this;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get locales.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Add a locale to the user.
     *
     * @param Locale $locale
     *
     * @return void
     */
    public function addLocale($locale)
    {
        $this->locales[] = $locale;
    }

    public function removeLocale(){
        $this->locales = new ArrayCollection();

        return $this;
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->created_at = new \DateTime("now");
        $this->updated_at = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updated_at = new \DateTime("now");
    }


    /**
     * @return the unknown_type
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param unknown_type $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param unknown_type $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getArrayCopy ()
    {
        return get_object_vars($this);
    }


    /**
    * setInputFilter
    * @param InputFilterInterface $inputFilter
    */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /**
    * getInputFilter
    *
    * @return  InputFilter $inputFilter
    */
    public function getInputFilter()
    {
         if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}