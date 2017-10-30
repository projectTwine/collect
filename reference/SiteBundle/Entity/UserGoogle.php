<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserGoogle
 */
class UserGoogle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $googleId;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $googleEmail;

    /**
     * @var string
     */
    private $userInfo;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

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
     * Set googleId
     *
     * @param string $googleId
     * @return UserGoogle
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
    
        return $this;
    }

    /**
     * Get googleId
     *
     * @return string 
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     * @return UserGoogle
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    
        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string 
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set googleEmail
     *
     * @param string $googleEmail
     * @return UserGoogle
     */
    public function setGoogleEmail($googleEmail)
    {
        $this->googleEmail = $googleEmail;
    
        return $this;
    }

    /**
     * Get googleEmail
     *
     * @return string 
     */
    public function getGoogleEmail()
    {
        return $this->googleEmail;
    }

    /**
     * Set userInfo
     *
     * @param string $userInfo
     * @return UserGoogle
     */
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;
    
        return $this;
    }

    /**
     * Get userInfo
     *
     * @return string 
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return UserGoogle
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
     * @return UserGoogle
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
     * Set user
     *
     * @param \ClassCentral\SiteBundle\Entity\User $user
     * @return UserGoogle
     */
    public function setUser(\ClassCentral\SiteBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \ClassCentral\SiteBundle\Entity\Users 
     */
    public function getUser()
    {
        return $this->user;
    }
}