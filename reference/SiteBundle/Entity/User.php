<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Users
 * @UniqueEntity(
 *  fields = "email",
 *  message = "An account with this email address already exists"
 * )
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var email
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $role;

    /**
     * @var boolean
     */
    private $isActive;

    private $salt;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var \DateTime
     */
    private $lastLogin;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $moocTrackerSearchTerms;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $moocTrackerCourses;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $userCourses;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $newsletters;

    /**
     * @var boolean
     */
    private $isverified;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $userPreferences;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reviews;

    /**
     * @var integer
     */
    private $signupType;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Userfb
     */
    private $fb;

    /**
     * @var \ClassCentral\SiteBundle\Entity\UserGoogle
     */
    private $google;

    /**
     * @var string
     */
    private $handle;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Profile
     */
    private $profile;

    /**
     * @var boolean
     */
    private $isPrivate;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $indepthReviews;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $credentialReviews;

    private $follows;


    const SIGNUP_TYPE_FORM = 1;
    const SIGNUP_TYPE_FACEBOOK = 2;
    const SIGNUP_TYPE_GOOGLE = 3;
    // Special User - George P Burdell used for including external reviews
    const SPECIAL_USER_ID = 6915;

    // Review user attached to anonymous review
    const REVIEW_USER_EMAIL = 'reviews@class-central.com';
    const REVIEW_USER_ID = 12486;

    public function  __construct()
    {
        $this->role = "ROLE_STUDENT";
        $this->isActive = true;
        $this->setCreated(new \DateTime());
        $this->moocTrackerCourses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userCourses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->moocTrackerSearchTerms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->newsletters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userPreferences = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reviews = new \Doctrine\Common\Collections\ArrayCollection();
        $this->indepthReviews = new \Doctrine\Common\Collections\ArrayCollection();
        $this->follows = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isverified = 0;
        $this->setSignupType(self::SIGNUP_TYPE_FORM);
    }

    public function __toString()
    {
        return $this->email;
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

    public function setId( $id )
    {
        $this->id = $id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Users
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set roles
     *
     * @param string $roles
     * @return Users
     */
    public function setRole($role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Users
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @var string
     */
    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            ) = unserialize($serialized);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return array($this->getRole());
    }


    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a formatted name for display purposes
     */
    public function getDisplayName()
    {
        if(empty($this->name))
        {
            return 'Class Central user';
        }
        else
        {
            return ucwords( strtolower($this->name) );
        }
    }

    public function getFirstName()
    {
        if(!empty($this->name))
        {
            $names = explode(' ', $this->name);
            return $names[0];
        }

        return 'User Menu';
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
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
     * @return User
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
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    
        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime 
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }


    /**
     * Add moocTrackerSearchTerms
     *
     * @param \ClassCentral\SiteBundle\Entity\MoocTrackerSearchTerm $moocTrackerSearchTerms
     * @return User
     */
    public function addMoocTrackerSearchTerm(\ClassCentral\SiteBundle\Entity\MoocTrackerSearchTerm $moocTrackerSearchTerms)
    {
        $this->moocTrackerSearchTerms[] = $moocTrackerSearchTerms;
    
        return $this;
    }

    /**
     * Remove moocTrackerSearchTerms
     *
     * @param \ClassCentral\SiteBundle\Entity\MoocTrackerSearchTerm $moocTrackerSearchTerms
     */
    public function removeMoocTrackerSearchTerm(\ClassCentral\SiteBundle\Entity\MoocTrackerSearchTerm $moocTrackerSearchTerms)
    {
        $this->moocTrackerSearchTerms->removeElement($moocTrackerSearchTerms);
    }

    /**
     * Get moocTrackerSearchTerms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMoocTrackerSearchTerms()
    {
        return $this->moocTrackerSearchTerms;
    }

    /**
     * Add moocTrackerCourses
     *
     * @param \ClassCentral\SiteBundle\Entity\MoocTrackerCourse $moocTrackerCourses
     * @return User
     */
    public function addMoocTrackerCourse(\ClassCentral\SiteBundle\Entity\MoocTrackerCourse $moocTrackerCourses)
    {
        $this->moocTrackerCourses[] = $moocTrackerCourses;
    
        return $this;
    }

    /**
     * Remove moocTrackerCourses
     *
     * @param \ClassCentral\SiteBundle\Entity\MoocTrackerCourse $moocTrackerCourses
     */
    public function removeMoocTrackerCourse(\ClassCentral\SiteBundle\Entity\MoocTrackerCourse $moocTrackerCourses)
    {
        $this->moocTrackerCourses->removeElement($moocTrackerCourses);
    }

    /**
     * Get moocTrackerCourses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMoocTrackerCourses()
    {
        return $this->moocTrackerCourses;
    }

    /**
     * Add userCourse
     *
     * @param \ClassCentral\SiteBundle\Entity\UserCourse $userCourse
     * @return User
     */
    public function addUserCourse(\ClassCentral\SiteBundle\Entity\UserCourse $userCourse)
    {
        $this->userCourses[] = $userCourse;

        return $this;
    }

    /**
     * Remove $userCourse
     *
     * @param \ClassCentral\SiteBundle\Entity\UserCourse $userCourse
     */
    public function removeUserCourse(\ClassCentral\SiteBundle\Entity\UserCourse $userCourse)
    {
        $this->userCourses->removeElement($userCourse);
    }

    /**
     * Get userCourses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserCourses()
    {
        return $this->userCourses;
    }

    public function addUserPreference(\ClassCentral\SiteBundle\Entity\UserPreference $userPreference)
    {
        $this->userPreferences[] = $userPreference;
        return $this;
    }

    public function getUserPreferences()
    {
        return $this->userPreferences;
    }

    public function getUserPreferencesByTypeMap()
    {
        $up = array();
        foreach($this->userPreferences as $userPreference)
        {
            $up[$userPreference->getType()] = $userPreference;
        }
        return $up;
    }

    /**
     * Add newsletters
     *
     * @param \ClassCentral\SiteBundle\Entity\Newsletter $newsletters
     * @return User
     */
    public function addNewsletter(\ClassCentral\SiteBundle\Entity\Newsletter $newsletters)
    {
        $this->newsletters[] = $newsletters;
    
        return $this;
    }

    /**
     * Remove newsletters
     *
     * @param \ClassCentral\SiteBundle\Entity\Newsletter $newsletters
     */
    public function removeNewsletter(\ClassCentral\SiteBundle\Entity\Newsletter $newsletters)
    {
        $this->newsletters->removeElement($newsletters);
    }

    /**
     * Get newsletters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNewsletters()
    {
        return $this->newsletters;
    }

    /**
     * Signs up for a newsletter
     */
    public function subscribe(\ClassCentral\SiteBundle\Entity\Newsletter $newsletter)
    {
        $signedUp = false;
        foreach($this->getNewsletters() as $ns)
        {
            if($ns->getCode() == $newsletter->getCode())
            {
                $signedUp = true;
                break;
            }
        }
        if(!$signedUp)
        {
            $this->addNewsletter($newsletter);
        }
    }

    /**
     * Given a password generates the hash out of it
     */
    public function getHashedPassword($password)
    {
        return password_hash($password,PASSWORD_BCRYPT,array("cost" => 10));
    }


    /**
     * Set isverified
     *
     * @param boolean $isverified
     * @return User
     */
    public function setIsverified($isverified)
    {
        $this->isverified = $isverified;
    
        return $this;
    }

    /**
     * Get isverified
     *
     * @return boolean 
     */
    public function getIsverified()
    {
        return $this->isverified;
    }

    /**
     * Add reviews
     *
     * @param \ClassCentral\SiteBundle\Entity\Review $reviews
     * @return User
     */
    public function addReview(\ClassCentral\SiteBundle\Entity\Review $reviews)
    {
        $this->reviews[] = $reviews;
    
        return $this;
    }

    /**
     * Remove reviews
     *
     * @param \ClassCentral\SiteBundle\Entity\Review $reviews
     */
    public function removeReview(\ClassCentral\SiteBundle\Entity\Review $reviews)
    {
        $this->reviews->removeElement($reviews);
    }

    /**
     * Get reviews
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Remove userPreferences
     *
     * @param \ClassCentral\SiteBundle\Entity\UserPreference $userPreferences
     */
    public function removeUserPreference(\ClassCentral\SiteBundle\Entity\UserPreference $userPreferences)
    {
        $this->userPreferences->removeElement($userPreferences);
    }


    /**
     * Set signupType
     *
     * @param integer $signupType
     * @return User
     */
    public function setSignupType($signupType)
    {
        $this->signupType = $signupType;
    
        return $this;
    }

    /**
     * Get signupType
     *
     * @return integer 
     */
    public function getSignupType()
    {
        return $this->signupType;
    }

    public function getSignupTypeString()
    {
        if($this->signupType == self::SIGNUP_TYPE_FACEBOOK)
        {
            return 'facebook';
        }

        return 'signup_form';
    }

    /**
     * Set fb
     *
     * @param \ClassCentral\SiteBundle\Entity\Userfb $fb
     * @return User
     */
    public function setFb(\ClassCentral\SiteBundle\Entity\UserFb $fb = null)
    {
        $this->fb = $fb;
    
        return $this;
    }

    /**
     * Get fb
     *
     * @return \ClassCentral\SiteBundle\Entity\Userfb 
     */
    public function getFb()
    {
        return $this->fb;
    }

    /**
     * Set Google
     *
     * @param \ClassCentral\SiteBundle\Entity\UserGoogle $fb
     * @return User
     */
    public function setGoogle(\ClassCentral\SiteBundle\Entity\UserGoogle $google = null)
    {
        $this->google = $google;

        return $this;
    }

    /**
     * Get Google
     *
     * @return \ClassCentral\SiteBundle\Entity\UserGoogle
     */
    public function getGoogle()
    {
        return $this->google;
    }


    /**
     * Set handle
     *
     * @param string $handle
     * @return User
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
    
        return $this;
    }

    /**
     * @return string username field stored in the database
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Set profile
     *
     * @param \ClassCentral\SiteBundle\Entity\Profile $profile
     * @return User
     */
    public function setProfile(\ClassCentral\SiteBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;
    
        return $this;
    }

    /**
     * Get profile
     *
     * @return \ClassCentral\SiteBundle\Entity\Profile 
     */
    public function getProfile()
    {
        return $this->profile;
    }


    /**
     * Set isPrivate
     *
     * @param boolean $isPrivate
     * @return User
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;
    
        return $this;
    }

    /**
     * Get isPrivate
     *
     * @return boolean 
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }


    /**
     * Add indepthReviews
     *
     * @param \ClassCentral\SiteBundle\Entity\IndepthReview $indepthReviews
     * @return User
     */
    public function addIndepthReview(\ClassCentral\SiteBundle\Entity\IndepthReview $indepthReviews)
    {
        $this->indepthReviews[] = $indepthReviews;
    
        return $this;
    }

    /**
     * Remove indepthReviews
     *
     * @param \ClassCentral\SiteBundle\Entity\IndepthReview $indepthReviews
     */
    public function removeIndepthReview(\ClassCentral\SiteBundle\Entity\IndepthReview $indepthReviews)
    {
        $this->indepthReviews->removeElement($indepthReviews);
    }

    /**
     * Get indepthReviews
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIndepthReviews()
    {
        return $this->indepthReviews;
    }

    /**
     * Add credentialReviews
     *
     * @param \ClassCentral\CredentialBundle\Entity\Credential $credentialReviews
     * @return User
     */
    public function addCredentialReview(\ClassCentral\CredentialBundle\Entity\Credential $credentialReviews)
    {
        $this->credentialReviews[] = $credentialReviews;
    
        return $this;
    }

    /**
     * Remove credentialReviews
     *
     * @param \ClassCentral\CredentialBundle\Entity\Credential $credentialReviews
     */
    public function removeCredentialReview(\ClassCentral\CredentialBundle\Entity\Credential $credentialReviews)
    {
        $this->credentialReviews->removeElement($credentialReviews);
    }

    /**
     * Get credentialReviews
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCredentialReviews()
    {
        return $this->credentialReviews;
    }

    public function getFollows()
    {
        return $this->follows;
    }

    public function removeFollow(Follow $follow)
    {
        $this->follows->removeElement($follow);
    }

    public function addFollow(Follow $follow)
    {
        $this->follows[] = $follow;
    }

    /**
     * Categorize item ids by item type
     * @return array
     */
    public function getFollowsCategorizedByItem()
    {
        $follows = array();
        foreach(Item::$items as $item)
        {
            $follows[$item] = array();
        }

        foreach($this->getFollows() as $follow)
        {
            $follows[$follow->getItem()][] = $follow->getItemId();
        }

        return $follows;
    }

    /**
     * For the recommendations page to work user needs to follow
     * atleast 1 subject. This function checks for that.
     */
    public function isFollowingASubject()
    {
        foreach($this->getFollows() as $follow)
        {
            if( $follow->getItem() == Item::ITEM_TYPE_SUBJECT )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Get User Course Ids
     */
    public function getUserCourseIds()
    {
        $courseIds = array();
        foreach($this->getUserCourses() as $uc)
        {
            $courseIds[] =$uc->getCourse()->getId();
        }
        return $courseIds;
    }

    public function areRecommendationsAvailable()
    {
        return (count($this->getFollows()) >= 10 ) && $this->isFollowingASubject();
    }

    public function getHoursSinceSignup()
    {
        $now = new \DateTime();
        $diff = $this->getCreated()->diff($now);
        $hours = $diff->h;
        $hours = $hours + ($diff->days*24);

        return $hours;
    }
}