<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\Language
 */
class Language
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
     * @var ClassCentral\SiteBundle\Entity\Course
     */
    private $courses;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $color;

    /**
     * @var integer
     */
    private $displayOrder;

    /*
     * Count of number of courses
     * @var integer
     */
    private $courseCount;

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
     * Set slug
     *
     * @param string $slug
     * @return Language
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Language
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
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
     * Remove courses
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $courses
     */
    public function removeCourse(\ClassCentral\SiteBundle\Entity\Course $courses)
    {
        $this->courses->removeElement($courses);
    }


    /**
     * Set color
     *
     * @param string $color
     * @return Language
     */
    public function setColor($color)
    {
        $this->color = $color;
    
        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set displayOrder
     *
     * @param integer $displayOrder
     * @return Language
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
    
        return $this;
    }

    /**
     * Get displayOrder
     *
     * @return integer 
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param mixed $courseCount
     */
    public function setCourseCount($courseCount)
    {
        $this->courseCount = $courseCount;
    }

    /**
     * @return mixed
     */
    public function getCourseCount()
    {
        return $this->courseCount;
    }
}