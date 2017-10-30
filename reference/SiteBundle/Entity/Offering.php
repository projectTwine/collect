<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * ClassCentral\SiteBundle\Entity\Offering
 */
class Offering {

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var date $startDate
     */
    private $startDate;

    /**
     * @var date $endDate
     */
    private $endDate;

    /**
     * @var boolean $exactDatesKnow
     */
    private $exactDatesKnow;

    /**
     * @var datetime $created
     */
    private $created;

    /**
     * @var datetime $modified
     */
    private $modified;

    /**
     * @var ClassCentral\SiteBundle\Entity\Course
     */
    private $course;

    /**
     *
     * @var string $url
     */
    private $url;

    /**
     * If this field is null then the course name will be displayed
     * @var string $name
     */
    private $name;

    /**
     *
     * @var string $videoIntro
     */
    private $videoIntro;

    /**
     * 
     * @var integer length
     */
    private $length;
    
    private $searchDesc;

    /**
     * This fields holds the status of the course. The values map as follows
     * 0 - Exact dates not know. Should show something like NA
     * 1 - Exact dates known
     * 2 - Exact dates not known, but month is availaible
     * 3 - Course is unavailaible and should not be shown anywhere
     * @var status
     */
    private $status;
    private $instructors;
    private $microdataDate;

    /**
     * Holds the state of the offering. A state must belong to:
     * one of these - finished,in progress, self paced, upcoming
     * and can also optionally belong to
     * recent, just announced
     * the field value is equal to the sum of required state values + optional values.
     * eg. if a new offering has just been added its state will be upcoming + just_announced.
     *  140 + 2 = 142
     * @var integer
     */
    private $state;

    // Different state values - optional
    const STATE_JUST_ANNOUNCED = 1;
    const STATE_RECENT = 2;
    const STATE_RECENT_AND_JUST_ANNOUNCED = 3;

    // Mutually exclusive state values - expired
    const STATE_FINISHED = 110;
    const STATE_IN_PROGRESS = 120;
    const STATE_SELF_PACED = 130;
    const STATE_UPCOMING  = 140;

    private $shortName;
    
    public static $types = array(
        'recent' => array('desc' => 'Recently started or starting soon','nav'=>'Recently started or starting soon','sessionDesc' => 'Recently started or starting soon'),
        'recentlyAdded' => array('desc' => 'Just Announced','nav'=>'Just Announced','sessionDesc' => 'Just Announced'),
        'ongoing' => array('desc' => 'In Progress', 'nav'=>'Courses in Progress', 'sessionDesc' => 'In progress'),
        'upcoming' => array('desc' => 'Upcoming courses', 'nav'=>'Future courses', 'sessionDesc' => 'Upcoming'),
        'selfpaced' => array('desc' => 'Self Paced', 'nav'=>'Self Paced', 'sessionDesc' => 'Self Paced'),
        'past' => array('desc' => 'Finished', 'nav'=>'Finished courses', 'sessionDesc' =>'Finished')
    );

    public static $stateMap = array(
        'recent' => self::STATE_RECENT,
        'recentlyAdded' => self::STATE_JUST_ANNOUNCED,
        'past' => self::STATE_FINISHED,
        'ongoing' => self::STATE_IN_PROGRESS,
        'selfpaced' => self::STATE_SELF_PACED,
        'upcoming' => self::STATE_UPCOMING
    );

    public function __construct() {
        $this->instructors = new ArrayCollection();
        $this->setCreated(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Set startDate
     *
     * @param date $startDate
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return date 
     */
    public function getStartDate() {
        return $this->startDate;
    }

    public function getStartTimestamp() {
        if($this->startDate) {
            return strval($this->startDate->getTimestamp());
        } else {
            return "";
        }
    }

    /**
     * Set endDate
     *
     * @param date $endDate
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return date 
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * Set exactDatesKnow
     *
     * @param boolean $exactDatesKnow
     */
    public function setExactDatesKnow($exactDatesKnow) {
        $this->exactDatesKnow = $exactDatesKnow;
    }

    /**
     * Get exactDatesKnow
     *
     * @return boolean 
     */
    public function getExactDatesKnow() {
        return $this->exactDatesKnow;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param datetime $modified
     */
    public function setModified($modified) {
        $this->modified = $modified;
    }

    /**
     * Get modified
     *
     * @return datetime 
     */
    public function getModified() {
        return $this->modified;
    }

    /**
     * Set course
     *
     * @param ClassCentral\SiteBundle\Entity\Course $course
     */
    public function setCourse(\ClassCentral\SiteBundle\Entity\Course $course) {
        $this->course = $course;
    }

    /**
     * Get course
     *
     * @return ClassCentral\SiteBundle\Entity\Course 
     */
    public function getCourse() {
        return $this->course;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        if (empty($this->name) && isset($this->course)) {
            return $this->course->getName();
        }

        return $this->name;
    }

    public function getDisplayDate() {
        switch ($this->status) {
            case self::START_DATES_UNKNOWN:
                return "NA";
                break;
            case self::START_DATES_KNOWN:
                return $this->getStartDate()->format('jS M, Y');
                break;
            case self::START_MONTH_KNOWN:
                return $this->getStartDate()->format('M, Y');
                break;
            case self::COURSE_OPEN:
                return "Self paced";    
            case self::START_YEAR_KNOWN:
                return $this->getStartDate()->format('Y');    
            default:
                return '';
        }
       
    }
    
    public function getMicrodataDate() {
        if($this->startDate) {
            return $this->getStartDate()->format('Y-m-d');
        } else {
            return "";
        }
    }
    public function getUrl() {        
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getVideoIntro() {
        return $this->videoIntro;
    }

    public function setVideoIntro($videoIntro) {
        $this->videoIntro = $videoIntro;
    }

    public function getLength() {
        return $this->length;
    }

    public function setLength($length) {
        $this->length = $length;
    }

    public function getInstructors() {
        return $this->instructors;
    }

    public function addInstructor(Instructor $instructor) {
        $this->instructors[] = $instructor;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function getSearchDesc() {
        return $this->searchDesc;
    }
    public function setSearchDesc($desc) {
        $this->searchDesc = $desc;
    }
    
    public function getInitiative() {
        return $this->course->getInitiative();
    }

    public function setShortName($shortName) {
        $this->shortName = $shortName;
    }

    public function getShortName() {
        return $this->shortName;
    }

    /**
    * Value that the status for offering can take
    *
    */
    const START_DATES_UNKNOWN = 0;
    const START_DATES_KNOWN = 1;
    const START_MONTH_KNOWN = 2;
    const COURSE_NA = 3;
    const COURSE_OPEN = 4; // Implies open registration
    const START_YEAR_KNOWN = 5;
  
    /**
    * Returns a list of statuses
    * @return array
    */
    public static function getStatuses(){
        return array(
            self::START_DATES_UNKNOWN => 'Start Dates Unknown',
            self::START_DATES_KNOWN => 'Start Dates Known',
            self::START_MONTH_KNOWN => 'Start Month Known',
            self::COURSE_NA => 'Course not available',
            self::COURSE_OPEN => 'Self paced',
            self::START_YEAR_KNOWN => 'Start Year Known'
        );
    }



    /**
     * Set state
     *
     * @param integer $state
     * @return Offering
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

}
