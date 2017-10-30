<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserPreference
 */
class UserPreference
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var string
     */
    private $value;

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


    const USER_PREFERENCE_MOOC_TRACKER_COURSES = 100;
    const USER_PREFERENCE_MOOC_TRACKER_SEARCH_TERM = 101;
    const USER_PREFERENCE_REVIEW_SOLICITATION = 102;
    const USER_PREFERENCE_FOLLOW_UP_EMAILs = 103;
    const USER_PREFERENCE_PERSONALIZED_COURSE_RECOMMENDATIONS = 104;
    const USER_PROFILE_UPDATE_EMAIL = 1000; // Stores the email address and token until the verification happens
    const USER_PROFILE_DELETE_ACCOUNT = 1001; // Marks a profile for deletion

    public static $validPrefs = array(
        self::USER_PREFERENCE_MOOC_TRACKER_SEARCH_TERM, self::USER_PREFERENCE_MOOC_TRACKER_COURSES, self::USER_PREFERENCE_REVIEW_SOLICITATION,
        self::USER_PREFERENCE_FOLLOW_UP_EMAILs, self::USER_PREFERENCE_PERSONALIZED_COURSE_RECOMMENDATIONS,
        self::USER_PROFILE_UPDATE_EMAIL, self::USER_PROFILE_DELETE_ACCOUNT
    );

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
     * Set type
     *
     * @param integer $type
     * @return UserPreference
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return UserPreference
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
     * @return UserPreference
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
     * @return UserPreference
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
     * @return UserPreference
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
