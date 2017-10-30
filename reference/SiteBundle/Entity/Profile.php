<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 */
class Profile
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $aboutMe;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $fieldOfStudy;

    /**
     * @var string
     */
    private $highestDegree;

    /**
     * @var string
     */
    private $twitter;

    /**
     * @var string
     */
    private $coursera;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $gplus;

    /**
     * @var string
     */
    private $linkedin;

    /**
     * @var string
     */
    private $facebook;

    /**
     * @var string
     */
    private $photo;

    /**
     * @var \ClassCentral\SiteBundle\Entity\User
     */
    private $user;

    /**
     * @var integer
     */
    private $score;

    /**
     * @var string
     */
    private $jobTitle;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;


    const DEFAULT_PROFILE_PIC = '/bundles/classcentralsite/slashpixel/images/profile-pic-placeholder.png';


    public static $degrees = array(
        'High School',
        'Associates Degree',
        'Bachelors Degree',
        'Masters Degree',
        'Master of Business Administration (M.B.A)',
        'Juris Doctor (J.D.)',
        'Doctor of Medicine (M.D)',
        'Doctor of Philosophy',
        'Other'
    );

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
     * Set aboutMe
     *
     * @param string $aboutMe
     * @return Profile
     */
    public function setAboutMe($aboutMe)
    {
        $this->aboutMe = $aboutMe;
    
        return $this;
    }

    /**
     * Get aboutMe
     *
     * @return string 
     */
    public function getAboutMe()
    {
        return $this->aboutMe;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Profile
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set fieldOfStudy
     *
     * @param string $fieldOfStudy
     * @return Profile
     */
    public function setFieldOfStudy($fieldOfStudy)
    {
        $this->fieldOfStudy = $fieldOfStudy;
    
        return $this;
    }

    /**
     * Get fieldOfStudy
     *
     * @return string 
     */
    public function getFieldOfStudy()
    {
        return $this->fieldOfStudy;
    }

    /**
     * Set highestDegree
     *
     * @param string $highestDegree
     * @return Profile
     */
    public function setHighestDegree($highestDegree)
    {
        $this->highestDegree = $highestDegree;
    
        return $this;
    }

    /**
     * Get highestDegree
     *
     * @return string 
     */
    public function getHighestDegree()
    {
        return $this->highestDegree;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return Profile
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    
        return $this;
    }

    /**
     * Get twitter
     *
     * @return string 
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set coursera
     *
     * @param string $coursera
     * @return Profile
     */
    public function setCoursera($coursera)
    {
        $this->coursera = $coursera;
    
        return $this;
    }

    /**
     * Get coursera
     *
     * @return string 
     */
    public function getCoursera()
    {
        return $this->coursera;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Profile
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set gplus
     *
     * @param string $gplus
     * @return Profile
     */
    public function setGplus($gplus)
    {
        $this->gplus = $gplus;
    
        return $this;
    }

    /**
     * Get gplus
     *
     * @return string 
     */
    public function getGplus()
    {
        return $this->gplus;
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     * @return Profile
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;
    
        return $this;
    }

    /**
     * Get linkedin
     *
     * @return string 
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return Profile
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
    
        return $this;
    }

    /**
     * Get facebook
     *
     * @return string 
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return Profile
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    
        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set user
     *
     * @param \ClassCentral\SiteBundle\Entity\User $user
     * @return Profile
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


    /**
     * Set score
     *
     * @param integer $score
     * @return Profile
     */
    public function setScore($score)
    {
        $this->score = $score;
    
        return $this;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set jobTitle
     *
     * @param string $jobTitle
     * @return Profile
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;
    
        return $this;
    }

    /**
     * Get jobTitle
     *
     * @return string 
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
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
}