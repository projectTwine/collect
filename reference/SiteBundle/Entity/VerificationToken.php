<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VerificationToken
 */
class VerificationToken
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $value;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * Number of minutes the token is valid since creation
     * @var integer
     */
    private $expiry;

    const EXPIRY_1_HOUR = 60;
    const EXPIRY_1_DAY = 1440;
    const EXPIRY_1_WEEK = 10080;
    const EXPIRY_1_YEAR = 525949;

    public function __construct()
    {
        $this->setCreated(new \DateTime());
        $this->expiry = self::EXPIRY_1_WEEK;
        // Generate a token
        $this->token = md5(openssl_random_pseudo_bytes(32));
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
     * Set token
     *
     * @param string $token
     * @return VerificationToken
     */
    /*
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }
    */

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return VerificationToken
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return VerificationToken
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
     * Set expiry
     *
     * @param integer $expiry
     * @return VerificationToken
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
    
        return $this;
    }

    /**
     * Get expiry
     *
     * @return integer 
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    public function setTokenValueArray($array)
    {
        $this->setValue(json_encode($array));
    }

    public function getTokenValueArray()
    {
        return json_decode($this->value,true);
    }
}
