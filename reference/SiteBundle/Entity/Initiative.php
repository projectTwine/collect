<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\Initiative
 */
class Initiative
{

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $url
     */
    private $url;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var string $code
     */
    private $code;
    
    /**
     * @var string $tooltip
     */
    private $tooltip;

    /**
     * @var string $imageUrl
     */
    private $imageUrl;


    /**
     * Number of courses
     * @var int
     */
    private $count;

    
    /**
     * List of initiative name to => CODE used for navigation
     * @var array type
     */
    public static $types = array(
        'coursera' => 'COURSERA',
        'udacity' => 'UDACITY',
        'edx' => 'EDX',
        'novoed' => 'NOVOED',
        'canvas' => 'CANVAS',
        'open2study' => 'OPEN2STUDY',
        'iversity' => 'IVERSITY',
        'futurelearn' => 'FUTURELEARN',
        'others' => 'OTHERS',
    );


    
    /**
     * @var ClassCentral\SiteBundle\Entity\Course
     */
    private $courses;

    public function __construct()
    {        
        $this->courses = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Add courses
     *
     * @param ClassCentral\SiteBundle\Entity\Course $courses
     */
    public function addCourse(\ClassCentral\SiteBundle\Entity\Course $courses)
    {
        $this->courses[] = $courses;
    }

    /**
     * Get course
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCourses()
    {
        return $this->courses;
    }
    
    public function __toString() {
        return $this->getName();
    }
    
    /**
     * Set tooltip
     *
     * @param string $tooltip
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;
    }

    /**
     * Get tooltip
     *
     * @return string 
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Gets the name of the directory on the cdn.
     * i.e providers/coursera.jpg
     */
    public function getImageDir()
    {
        return "providers";
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}