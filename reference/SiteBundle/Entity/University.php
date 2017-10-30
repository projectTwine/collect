<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\University
 */
class University
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
     * @var ClassCentral\SiteBundle\Entity\Instructor
     */
    private $instructors;

    public function __construct()
    {
        $this->instructors = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add instructors
     *
     * @param ClassCentral\SiteBundle\Entity\Instructor $instructors
     */
    public function addInstructor(\ClassCentral\SiteBundle\Entity\Instructor $instructors)
    {
        $this->instructors[] = $instructors;
    }

    /**
     * Get instructors
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInstructors()
    {
        return $this->instructors;
    }
}