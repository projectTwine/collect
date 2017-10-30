<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReviewFeedbackSummary
 */
class ReviewFeedbackSummary
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $positive;

    /**
     * @var integer
     */
    private $negative;

    /**
     * @var integer
     */
    private $total;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

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
     * Set positive
     *
     * @param integer $positive
     * @return ReviewFeedbackSummary
     */
    public function setPositive($positive)
    {
        $this->positive = $positive;
    
        return $this;
    }

    /**
     * Get positive
     *
     * @return integer 
     */
    public function getPositive()
    {
        return $this->positive;
    }

    /**
     * Set negative
     *
     * @param integer $negative
     * @return ReviewFeedbackSummary
     */
    public function setNegative($negative)
    {
        $this->negative = $negative;
    
        return $this;
    }

    /**
     * Get negative
     *
     * @return integer 
     */
    public function getNegative()
    {
        return $this->negative;
    }

    /**
     * Set total
     *
     * @param integer $total
     * @return ReviewFeedbackSummary
     */
    public function setTotal($total)
    {
        $this->total = $total;
    
        return $this;
    }

    /**
     * Get total
     *
     * @return integer 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ReviewFeedbackSummary
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
     * @return ReviewFeedbackSummary
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
     * Set review
     *
     * @param \ClassCentral\SiteBundle\Entity\Review $review
     * @return ReviewFeedbackSummary
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
