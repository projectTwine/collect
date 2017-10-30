<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TextAd
 */
class TextAd
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $displayUrl;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $url;

    /**
     * @var boolean
     */
    private $visible;

    /**
     * @var string
     */
    private $providerName;

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
     * @param string $title
     * @return TextAd
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
     * Set displayUrl
     *
     * @param string $displayUrl
     * @return TextAd
     */
    public function setDisplayUrl($displayUrl)
    {
        $this->displayUrl = $displayUrl;
    
        return $this;
    }

    /**
     * Get displayUrl
     *
     * @return string 
     */
    public function getDisplayUrl()
    {
        return $this->displayUrl;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return TextAd
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return TextAd
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
     * Set visible
     *
     * @param boolean $visible
     * @return TextAd
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    
        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }



    /**
     * Set providerName
     *
     * @param string $providerName
     * @return TextAd
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    
        return $this;
    }

    /**
     * Get providerName
     *
     * @return string 
     */
    public function getProviderName()
    {
        return $this->providerName;
    }
}