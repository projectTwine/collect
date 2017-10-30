<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MoocTrackerSearchTerm
 */
class MoocTrackerSearchTerm
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $searchTerm;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \ClassCentral\SiteBundle\Entity\User
     */
    private $user;


    public function __construct()
    {
        $this->setCreated(new \DateTime());
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
     * Set searchTerm
     *
     * @param string $searchTerm
     * @return MoocTrackerSearchTerm
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;
    
        return $this;
    }

    /**
     * Get searchTerm
     *
     * @return string 
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return MoocTrackerSearchTerm
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
     * Set user
     *
     * @param \ClassCentral\SiteBundle\Entity\User $user
     * @return MoocTrackerSearchTerm
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
