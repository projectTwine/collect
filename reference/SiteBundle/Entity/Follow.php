<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Follow
 */
class Follow
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $item;

    /**
     * @var integer
     */
    private $itemId;

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
     * Set item
     *
     * @param string $item
     * @return Follow
     */
    public function setItem($item)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return string 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set itemId
     *
     * @param integer $itemId
     * @return Follow
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    
        return $this;
    }

    /**
     * Get itemId
     *
     * @return integer 
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Follow
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
     * @return UserCourse
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
