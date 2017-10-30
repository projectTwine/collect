<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserFb
 */
class UserFb
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fbId;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $fbEmail;

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
     * Set fbId
     *
     * @param string $fbId
     * @return UserFb
     */
    public function setFbId($fbId)
    {
        $this->fbId = $fbId;
    
        return $this;
    }

    /**
     * Get fbId
     *
     * @return string 
     */
    public function getFbId()
    {
        return $this->fbId;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     * @return UserFb
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
     * Set fbEmail
     *
     * @param string $fbEmail
     * @return UserFb
     */
    public function setFbEmail($fbEmail)
    {
        $this->fbEmail = $fbEmail;
    
        return $this;
    }

    /**
     * Get fbEmail
     *
     * @return string 
     */
    public function getFbEmail()
    {
        return $this->fbEmail;
    }

    /**
     * Set userInfo
     *
     * @param string $userInfo
     * @return UserFb
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
     * @return UserFb
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
     * @return UserFb
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
     * @return UserFb
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
