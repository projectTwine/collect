<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\News
 */
class News
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var text $title
     */
    private $title;

    /**
     * @var text $url
     */
    private $url;

    /**
     * @var datetime $created
     */
    private $created;

    /**
     * @var datetime $modified
     */
    private $modified;

    private $description;

    private $localImageUrl;

    private $remoteImageUrl;

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * Set title
     *
     * @param text $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return text
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get url
     *
     * @return text 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set url
     *
     * @param text $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }



    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get modified
     *
     * @return datetime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($desc)
    {
        $this->description = $desc;
    }

    public function getLocalImageUrl()
    {
        return $this->localImageUrl;
    }

    public function setLocalImageUrl($imageUrl)
    {
        $this->localImageUrl = $imageUrl;
    }

    public function getRemoteImageUrl()
    {
        return $this->remoteImageUrl;
    }

    public function setRemoteImageUrl($imageUrl)
    {
        $this->remoteImageUrl = $imageUrl;
    }
}