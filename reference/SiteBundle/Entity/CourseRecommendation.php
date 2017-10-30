<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourseRecommendation
 */
class CourseRecommendation
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
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Course
     */
    private $course;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Course
     */
    private $recommendedCourse;


    public function  __construct()
    {
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
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
     * Set position
     *
     * @param integer $position
     * @return CourseRecommendation
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
     * Set created
     *
     * @param \DateTime $created
     * @return CourseRecommendation
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return CourseRecommendation
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    
        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set course
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $course
     * @return CourseRecommendation
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

    /**
     * Set recommendedCourse
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $recommendedCourse
     * @return CourseRecommendation
     */
    public function setRecommendedCourse(\ClassCentral\SiteBundle\Entity\Course $recommendedCourse = null)
    {
        $this->recommendedCourse = $recommendedCourse;
    
        return $this;
    }

    /**
     * Get recommendedCourse
     *
     * @return \ClassCentral\SiteBundle\Entity\Course 
     */
    public function getRecommendedCourse()
    {
        return $this->recommendedCourse;
    }
}
