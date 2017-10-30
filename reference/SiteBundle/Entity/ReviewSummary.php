<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReviewSummary
 */
class ReviewSummary
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
     * @var \DateTime
     */
    private $created;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Review
     */
    private $review;

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
     * @return ReviewSummary
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
     * Set created
     *
     * @param \DateTime $created
     * @return ReviewSummary
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
     * @return ReviewSummary
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
}
