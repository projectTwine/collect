<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Interview
 */
class Interview
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $instructorName;

    /**
     * @var string
     */
    private $instructorPhoto;

    /**
     * @var string
     */
    private $url;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $courses;


    public function __construct()
    {
        $this->created = new \DateTime();
        $this->courses = new ArrayCollection();
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
     * Set summary
     *
     * @param string $summary
     * @return Interview
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    
        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Interview
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
     * Set instructorName
     *
     * @param string $instructorName
     * @return Interview
     */
    public function setInstructorName($instructorName)
    {
        $this->instructorName = $instructorName;
    
        return $this;
    }

    /**
     * Get instructorName
     *
     * @return string 
     */
    public function getInstructorName()
    {
        return $this->instructorName;
    }

    /**
     * Set instructorPhoto
     *
     * @param string $instructorPhoto
     * @return Interview
     */
    public function setInstructorPhoto($instructorPhoto)
    {
        $this->instructorPhoto = $instructorPhoto;
    
        return $this;
    }

    /**
     * Get instructorPhoto
     *
     * @return string 
     */
    public function getInstructorPhoto()
    {
        return $this->instructorPhoto;
    }

    /**
     * Set Url
     *
     * @param string $url
     * @return Interview
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get instructorPhoto
     *
     * @return string
     */
    public function getUrl()
    {
        return  $this->url;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Interview
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
     * @return Interview
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
     * Add courses
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $courses
     * @return Interview
     */
    public function addCourse(\ClassCentral\SiteBundle\Entity\Course $courses)
    {
        $this->courses[] = $courses;
    
        return $this;
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
     * Get courses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCourses()
    {
        return $this->courses;
    }

    public function __toString()
    {
        return $this->getInstructorName();
    }
}