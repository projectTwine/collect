<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\Instructor
 */
class Instructor {

    /**
     * @var integer $id
     */
    private $id;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $homepage
     */
    private $homepage;
    
    private $offerings;

    /**
     *
     * @var ClassCentral\SiteBundle\Entity\Course
     */
    private $courses;

    /**
     *
     * @var ClassCentral\SiteBundle\Entity\University
     */
    private $university;

    public function __construct() {
        $this->offerings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->courses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set homepage
     *
     * @param string $homepage
     */
    public function setHomepage($homepage) {
        $this->homepage = $homepage;
    }

    /**
     * Get homepage
     *
     * @return string 
     */
    public function getHomepage() {
        return $this->homepage;
    }

    public function getOfferings() {
        return $this->offerings();
    }

    public function getCourses() {
        return $this->courses();
    }

    /**
     * Set university
     * 
     * @param ClassCentral\SiteBundle\Entity\University $university
     */
    public function setUniversity(\ClassCentral\SiteBundle\Entity\University $university) {
        $this->university = $university;
    }

    /**
     * Get university
     * 
     * @return ClassCentral\SiteBundle\Entity\University
     */
    public function getUniversity() {
        return $this->getUniversity;
    }

    public function __toString() {
        return $this->getName();
    }

}
