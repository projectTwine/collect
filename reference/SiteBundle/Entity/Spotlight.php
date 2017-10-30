<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Spotlight
 */
class Spotlight
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Course
     */
    private $course;

    private $provider;

    private $courseId;

    const SPOTLIGHT_TYPE_DEMO = 1; // Only show in dev
    const SPOTLIGHT_TYPE_COURSE = 2;
    const SPOTLIGHT_TYPE_NEWS = 3;
    const SPOTLIGHT_TYPE_INTERVIEW = 4;
    const SPOTLIGHT_TYPE_AD = 5;
    const SPOTLIGHT_TYPE_CREDENTIAL = 6;
    const SPOTLIGHT_TYPE_BLOG = 100; // This is for the spotlight blog section on the homepage

    public static $spotlightMap = array(
        self::SPOTLIGHT_TYPE_DEMO => array( 'class' => 'spotlight-article','text' => 'View Demo', 'visible'=>"") ,
        self::SPOTLIGHT_TYPE_COURSE => array( 'class' => 'spotlight-course','text' => 'View Course', 'visible'=>"") ,
        self::SPOTLIGHT_TYPE_NEWS => array( 'class' => 'spotlight-article','text' => 'Read Article', 'visible' => ""),
        self::SPOTLIGHT_TYPE_INTERVIEW => array( 'class' => 'spotlight-interview','text' => 'Read Interview', 'visible' => ""),
        self::SPOTLIGHT_TYPE_AD => array( 'class' => 'spotlight-sponsor','text' => 'View Sponsor', 'visible' => "visible-lg"),
        self::SPOTLIGHT_TYPE_BLOG => array('class' =>'','text','View Blog','visible' => ''),
        self::SPOTLIGHT_TYPE_CREDENTIAL => array( 'class' => 'spotlight-course','text' => 'View Nanodegree', 'visible'=>"") ,
    );

    public static $spotlights = array(
        self::SPOTLIGHT_TYPE_DEMO => 'Demo',
        self::SPOTLIGHT_TYPE_COURSE => 'Course',
        self::SPOTLIGHT_TYPE_NEWS => 'News',
        self::SPOTLIGHT_TYPE_INTERVIEW => 'Interview',
        self::SPOTLIGHT_TYPE_AD => 'Advertisement',
        self::SPOTLIGHT_TYPE_BLOG => 'Blog',
        self::SPOTLIGHT_TYPE_CREDENTIAL => 'Credential'
    );


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Spotlight
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Spotlight
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Spotlight
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Spotlight
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     * @return Spotlight
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    
        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string 
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }
    /**
     * @var integer
     */
    private $type;


    /**
     * Set type
     *
     * @param integer $type
     * @return Spotlight
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set course
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $course
     * @return Spotlight
     */
    public function setCourse(\ClassCentral\SiteBundle\Entity\Course $course = null)
    {
        $this->course = $course;
    
        return $this;
    }

    /**
     * Get course
     *
     * @return \ClassCentral\SiteBundle\Entity\Course 
     */
    public function getCourse()
    {
        return $this->course;
    }

    public function getCourseId()
    {
        return $this->courseId;
    }

    public function setCourseId ($courseId)
    {
        $this->courseId = $courseId;

        return $this;
    }

    public function __sleep()
    {
        return array('id','position','title','title','description','url','imageUrl','type','courseId','provider');
    }

    public function getProvider()
    {
        if($this->provider)
        {
            return $this->provider;
        }
        else
        {
            if($this->getCourse() && $this->getCourse()->getInitiative() )
            {
                $this->provider = $this->getCourse()->getInitiative()->getName();
                return $this->provider;
            }

            return 'No Provider Name';
        }
    }


    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

}