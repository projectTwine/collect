<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FollowCounts
 */
class FollowCounts
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
     * @var integer
     */
    private $followed;

    /**
     * @var \DateTime
     */
    private $modified;


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
     * @return FollowCounts
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
     * @return FollowCounts
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
     * Set followed
     *
     * @param integer $followed
     * @return FollowCounts
     */
    public function setFollowed($followed)
    {
        $this->followed = $followed;
    
        return $this;
    }

    /**
     * Get followed
     *
     * @return integer 
     */
    public function getFollowed()
    {
        return $this->followed;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return FollowCounts
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
}
