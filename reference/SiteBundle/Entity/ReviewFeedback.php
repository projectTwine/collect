<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReviewFeedback
 */
class ReviewFeedback
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $helpful;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Review
     */
    private $review;

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
     * Set helpful
     *
     * @param boolean $helpful
     * @return ReviewFeedback
     */
    public function setHelpful($helpful)
    {
        $this->helpful = $helpful;
    
        return $this;
    }

    /**
     * Get helpful
     *
     * @return boolean 
     */
    public function getHelpful()
    {
        return $this->helpful;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ReviewFeedback
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
     * Set review
     *
     * @param \ClassCentral\SiteBundle\Entity\Review $review
     * @return ReviewFeedback
     */
    public function setReview(\ClassCentral\SiteBundle\Entity\Review $review = null)
    {
        $this->review = $review;
    
        return $this;
    }

    /**
     * Get review
     *
     * @return \ClassCentral\SiteBundle\Entity\Review 
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Set user
     *
     * @param \ClassCentral\SiteBundle\Entity\User $user
     * @return ReviewFeedback
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
    /**
     * @var string
     */
    private $sessionId;


    /**
     * Set sessionId
     *
     * @param string $sessionId
     * @return ReviewFeedback
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    
        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string 
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
}