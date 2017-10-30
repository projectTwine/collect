<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IndepthReview
 */
class IndepthReview
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
    private $url;

    /**
     * @var integer
     */
    private $rating;

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
     * @var \ClassCentral\SiteBundle\Entity\User
     */
    private $user;

    public function __construct()
    {
        $this->created = new \DateTime();
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
     * @return IndepthReview
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
     * Set url
     *
     * @param string $url
     * @return IndepthReview
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
     * Set rating
     *
     * @param integer $rating
     * @return IndepthReview
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    
        return $this;
    }

    /**
     * Get rating
     *
     * @return integer 
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return IndepthReview
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
     * @return IndepthReview
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
     * @return IndepthReview
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
     * Set user
     *
     * @param \ClassCentral\SiteBundle\Entity\User $user
     * @return IndepthReview
     */
    public function setUser(\ClassCentral\SiteBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \ClassCentral\SiteBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
