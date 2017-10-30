<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsletterLog
 */
class NewsletterLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $sent;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Newsletter
     */
    private $newsletter;

    public function __construct()
    {
        $this->sent = new \DateTime();
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
     * Set sent
     *
     * @param \DateTime $sent
     * @return NewsletterLog
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    
        return $this;
    }

    /**
     * Get sent
     *
     * @return \DateTime 
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return NewsletterLog
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
     * Set newsletter
     *
     * @param \ClassCentral\SiteBundle\Entity\Newsletter $newsletter
     * @return NewsletterLog
     */
    public function setNewsletter(\ClassCentral\SiteBundle\Entity\Newsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;
    
        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \ClassCentral\SiteBundle\Entity\Newsletters 
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }
}
