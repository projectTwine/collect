<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Review
 */
class Review
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var float
     */
    private $rating;

    /**
     * @var string
     */
    private $review;

    /**
     * @var integer
     */
    private $hours;

    /**
     * Easy, Medium, Hard, etc.
     * @var integer
     */
    private $difficultyId;

    /**
     * Beginner, Intermediate, Advanced
     * @var integer
     */
    private $levelId;

    /**
     * Completed, Dropped, etc.
     * @var integer
     */
    private $listId;

    /**
     * @var \ClassCentral\SiteBundle\Entity\User
     */
    private $user;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Course
     */
    private $course;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Offering
     */
    private $offering;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     *
     * @var status
     */
    private $status;

    /**
     * @var \ClassCentral\SiteBundle\Entity\ReviewFeedbackSummary
     */
    private $fbSummary;

    /**
     * @var string
     */
    private $externalLink;

    /**
     * @var string
     */
    private $reviewerName;

    /**
     * @var string
     */
    private $source;

    /**
     * @var boolean
     */
    private $isRating;

    /**
     * @var \ClassCentral\SiteBundle\Entity\ReviewSummary
     */
    private $reviewSummary;

    /**
     * @var integer
     */
    private $score;


    // Level
    const LEVEL_BEGINNER = 1;
    const LEVEL_INTERMEDIATE = 2;
    const LEVEL_ADVANCED = 3;

    public static $levels = array(
        self::LEVEL_BEGINNER => array('slug'=>'beginner','desc'=>'Beginner'),
        self::LEVEL_INTERMEDIATE => array('slug'=>'intermediate','desc'=>'Intermediate'),
        self::LEVEL_ADVANCED => array('slug'=>'advanced','desc'=>'Advanced'),
    );

    // Difficulty
    const DIFFICULTY_VERY_EASY = 1;
    const DIFFICULTY_EASY = 2;
    const DIFFICULTY_MEDIUM = 3;
    const DIFFICULTY_HARD = 4;
    const DIFFICULTY_VERY_HARD = 5;

    public static $difficulty = array(
        self::DIFFICULTY_VERY_EASY => array('slug'=>'very_easy', 'desc' => 'Very Easy'),
        self::DIFFICULTY_EASY => array('slug'=>'easy', 'desc' => 'Easy'),
        self::DIFFICULTY_MEDIUM => array('slug'=>'medium', 'desc' => 'Medium'),
        self::DIFFICULTY_HARD => array('slug'=>'hard', 'desc' => 'Hard'),
        self::DIFFICULTY_VERY_HARD => array('slug'=>'very_hard', 'desc' => 'Very Hard'),
    );

    /**
     * Statuses for reviews
     * Anything above 100 is not shown to the user
     */
    const REVIEW_NOT_SHOWN_STATUS_LOWER_BOUND = 100;

    const REVIEW_STATUS_NEW_BUT_SHOWN = 1;
    const REVIEW_STATUS_APPROVED = 2;
    const REVIEW_STATUS_NOT_SHOWN = 100;
    const REVIEW_STATUS_SPAM = 101;

    public static $statuses = array(
        self::REVIEW_STATUS_NEW_BUT_SHOWN => 'New Review - but shown',
        self::REVIEW_STATUS_APPROVED => 'Approved',
        self::REVIEW_STATUS_NOT_SHOWN => "Don't Show",
        self::REVIEW_STATUS_SPAM => 'Spam Review'
    );

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->setStatus(self::REVIEW_STATUS_NEW_BUT_SHOWN);
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
     * Set rating
     *
     * @param float $rating
     * @return Review
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    
        return $this;
    }

    /**
     * Get rating
     *
     * @return float 
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set review
     *
     * @param string $review
     * @return Review
     */
    public function setReview($review)
    {
        $this->review = $review;
    
        return $this;
    }

    /**
     * Get review
     *
     * @return string 
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Set hours
     *
     * @param integer $hours
     * @return Review
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    
        return $this;
    }

    /**
     * Get hours
     *
     * @return integer 
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set difficultyId
     *
     * @param integer $difficultyId
     * @return Review
     */
    public function setDifficultyId($difficultyId)
    {
        $this->difficultyId = $difficultyId;
    
        return $this;
    }

    /**
     * Get difficultyId
     *
     * @return integer 
     */
    public function getDifficultyId()
    {
        return $this->difficultyId;
    }

    /**
     * Set levelId
     *
     * @param integer $levelId
     * @return Review
     */
    public function setLevelId($levelId)
    {
        $this->levelId = $levelId;
    
        return $this;
    }

    /**
     * Get levelId
     *
     * @return integer 
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * Set listId
     *
     * @param integer $listId
     * @return Review
     */
    public function setListId($listId)
    {
        $this->listId = $listId;
    
        return $this;
    }

    /**
     * Get listId
     *
     * @return integer 
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Set user
     *
     * @param \ClassCentral\SiteBundle\Entity\User $user
     * @return Review
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
     * Set course
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $course
     * @return Review
     */
    public function setCourse(\ClassCentral\SiteBundle\Entity\Course $course = null)
    {
        $this->course = $course;
    
        return $this;
    }

    /**
     * Get course
     *
     * @return \ClassCentral\SiteBundle\Entity\Course 
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set offering
     *
     * @param \ClassCentral\SiteBundle\Entity\Offering $offering
     * @return Review
     */
    public function setOffering(\ClassCentral\SiteBundle\Entity\Offering $offering = null)
    {
        $this->offering = $offering;
    
        return $this;
    }

    /**
     * Get offering
     *
     * @return \ClassCentral\SiteBundle\Entity\Offering 
     */
    public function getOffering()
    {
        return $this->offering;
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

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }


    /**
     * Set fbSummary
     *
     * @param \ClassCentral\SiteBundle\Entity\ReviewFeedbackSummary $fbSummary
     * @return Review
     */
    public function setFbSummary(\ClassCentral\SiteBundle\Entity\ReviewFeedbackSummary $fbSummary = null)
    {
        $this->fbSummary = $fbSummary;
    
        return $this;
    }

    /**
     * Get fbSummary
     *
     * @return \ClassCentral\SiteBundle\Entity\ReviewFeedbackSummary 
     */
    public function getFbSummary()
    {
        return $this->fbSummary;
    }

    public function getProgress()
    {
        if($this->getListId())
        {
            return UserCourse::$progress[$this->getListId()]['desc'];
        }

        return "";
    }

    public function getDifficulty()
    {
        if($this->getDifficultyId())
        {
            return self::$difficulty[$this->getDifficultyId()]['desc'];
        }

        return "";
    }


    /**
     * Set externalLink
     *
     * @param string $externalLink
     * @return Review
     */
    public function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;
    
        return $this;
    }

    /**
     * Get externalLink
     *
     * @return string 
     */
    public function getExternalLink()
    {
        return $this->externalLink;
    }

    /**
     * Set reviewerName
     *
     * @param string $reviewerName
     * @return Review
     */
    public function setReviewerName($reviewerName)
    {
        $this->reviewerName = $reviewerName;
    
        return $this;
    }

    /**
     * Get reviewerName
     *
     * @return string 
     */
    public function getReviewerName()
    {
        return $this->reviewerName;
    }


    /**
     * Set source
     *
     * @param string $source
     * @return Review
     */
    public function setSource($source)
    {
        $this->source = $source;
    
        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set isRating
     *
     * @param boolean $isRating
     * @return Review
     */
    public function setIsRating($isRating)
    {
        $this->isRating = $isRating;
    
        return $this;
    }

    /**
     * Get isRating
     *
     * @return boolean 
     */
    public function getIsRating()
    {
        return $this->isRating;
    }


    /**
     * Set reviewSummary
     *
     * @param \ClassCentral\SiteBundle\Entity\ReviewSummary $reviewSummary
     * @return Review
     */
    public function setReviewSummary(\ClassCentral\SiteBundle\Entity\ReviewSummary $reviewSummary = null)
    {
        $this->reviewSummary = $reviewSummary;
    
        return $this;
    }

    /**
     * Get reviewSummary
     *
     * @return \ClassCentral\SiteBundle\Entity\ReviewSummary
     */
    public function getReviewSummary()
    {
        return $this->reviewSummary;
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
}