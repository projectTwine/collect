<?php

namespace ClassCentral\SiteBundle\Entity;

use ClassCentral\SiteBundle\Formatters\DefaultCourseFormatter;
use ClassCentral\CredentialBundle\Entity\Credential;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ClassCentral\SiteBundle\Entity\Course
 */
class Course {


    const PRICE_PERIOD_MONTHLY         = 'M';
    const PRICE_PERIOD_TOTAL           = 'T';
    const WORKLOAD_TYPE_HOURS_PER_WEEK = 'W';
    const WORKLOAD_TYPE_TOTAL_HOURS    = 'T';
    const PAID_CERTIFICATE = -1; // Special price to denote that the certificate is paid but price not known.

    public static $PRICE_PERIODS = array(
        self::PRICE_PERIOD_TOTAL => 'Total',
        self::PRICE_PERIOD_MONTHLY =>'Monthly'
    );

    public static $WORKLOAD = array(
        self::WORKLOAD_TYPE_HOURS_PER_WEEK => 'Hours Per Week',
        self::WORKLOAD_TYPE_TOTAL_HOURS => 'Total Hours',
    );


    public static $providersWithFavicons = array(
        'canvas','coursera','edraak','edx','futurelearn','iversity',
        'novoed','open2study','janux','openhpi','10gen','ce','stanford',
        'gatech-oms-cs','miriadax','acumen', 'udacity'
    );


    public function __construct() {
        $this->offerings = new ArrayCollection();   
        $this->institutions = new ArrayCollection();
        $this->instructors = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->setCreated(new \DateTime());
        $this->setStatus(CourseStatus::TO_BE_REVIEWED);
        $this->reviews = new \Doctrine\Common\Collections\ArrayCollection();
        $this->credentials = new \Doctrine\Common\Collections\ArrayCollection();
        $this->careers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->subjects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->price = 0;
        $this->pricePeriod = self::PRICE_PERIOD_TOTAL;
        $this->setIsMooc(true);
    }

    const THUMBNAIL_BASE_URL = 'https://d3r3mog6nu8pt4.cloudfront.net/spotlight/courses/';
    /**
     * @var integer $id
     */
    private $id;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var date $start_date
     */
    private $start_date;

    /**
     * @var boolean $exact_date_known
     */
    private $exact_date_known;

    /**
     * @var integer $stream_id
     */
   // private $stream_id;

    /**
     * @var ClassCentral\SiteBundle\Entity\Stream
     */
    private $stream;
    protected $offerings;    
    
     /**
     * @var ClassCentral\SiteBundle\Entity\Initiative
     */
    private $initiative;

    /**
     * @var ClassCentral\SiteBundle\Entity\Language
     */
    private $language;
    
    /**
     * @var ClassCentral\SiteBundle\Entity\Institution
     */
    private $institutions;

    /**
     *
     * @var integer length
     */
    private $length;

    private $searchDesc;

    /**
     *
     * @var status
     */
    private $status;

    /**
     * @var ClassCentral\SiteBundle\Entity\Instructor
     */
    private $instructors;

    private $shortName;

    private $description;

    /**
     * @var datetime $created
     */
    private $created;

    /**
     * @var datetime $modified
     */
    private $modified;

    /**
     *
     * @var string $url
     */
    private $url;

    /*
     * Generated url for the course page
     */
    private $slug;

    /**
     *
     * @var string $videoIntro
     */
    private $videoIntro;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reviews;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tags;


    /**
     * @var string
     */
    private $longDescription;

    /**
     * @var string
     */
    private $syllabus;

    /**
     * @var boolean
     */
    private $certificate;

    /**
     * @var boolean
     */
    private $verifiedCertificate;

    /**
     * @var integer
     */
    private $workloadMin;

    /**
     * @var integer
     */
    private $workloadMax;

    /**
     * @var string
     */
    private $oneliner;

    /**
     * @var string
     */
    private $thumbnail;

    /**
     * If exists the course page is redirected to the duplicate course
     * @var \ClassCentral\SiteBundle\Entity\Course
     */
    private $duplicateCourse;

    /**
     * @var \ClassCentral\SiteBundle\Entity\IndepthReview
     */
    private $indepthReview;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Interview
     */
    private $interview;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $credentials;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $careers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $subjects;

    /**
     * @var integer
     */
    private $price;

    /**
     * @var string
     */
    private $pricePeriod;

    /**
     * @var integer
     */
    private $certificatePrice;

    /**
     * @var string
     */
    private $workloadType;

    /**
     * @var integer
     */
    private $durationMin;

    /**
     * @var integer
     */
    private $durationMax;

    /**
     * @var string
     */
    private $durationType;

    /**
     * @var string
     */
    private $isMooc;


    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set start_date
     *
     * @param date $startDate
     */
    public function setStartDate($startDate) {
        $this->start_date = $startDate;
    }

    /**
     * Get start_date
     *
     * @return date 
     */
    public function getStartDate() {
        return $this->start_date;
    }

    /**
     * Set exact_date_known
     *
     * @param boolean $exactDateKnown
     */
    public function setExactDateKnown($exactDateKnown) {
        $this->exact_date_known = $exactDateKnown;
    }

    /**
     * Get exact_date_known
     *
     * @return boolean 
     */
    public function getExactDateKnown() {
        return $this->exact_date_known;
    }

    /**
     * Set stream
     *
     * @param ClassCentral\SiteBundle\Entity\Stream $stream
     */
    public function setStream(\ClassCentral\SiteBundle\Entity\Stream $stream) {
        $this->stream = $stream;
    }

    /**
     * Get stream
     *
     * @return ClassCentral\SiteBundle\Entity\Stream 
     */
    public function getStream() {
        return $this->stream;
    }

    public function addOffering(Offering $offering)
    {
        $this->offerings[] = $offering;
    }

    public function getOfferings() {
        return $this->offerings;
    }
    
    /**
     * Set initiative
     * 
     * @param ClassCEntral\SiteBundle\Entitiy\Offering $offering
     */
    public function setInitiative(\ClassCentral\SiteBundle\Entity\Initiative $initiative = null) {
        $this->initiative = $initiative;
    }

    /**
     * Get Initative
     * 
     * @return ClassCentral\SiteBundle\Entity\Initiative
     */
    public function getInitiative() {
        return $this->initiative;
    }

    /**
     * @param Language $lang
     */
    public function setLanguage(\ClassCentral\SiteBundle\Entity\Language $lang) {
        $this->language = $lang;
    }

    /**
     * @return ClassCentral\SiteBundle\Entity\Language
     */
    public  function getLanguage() {
        return $this->language;
    }
    
     /**
     * Add institution
     *
     * @param ClassCentral\SiteBundle\Entity\Institution $institutions
     */
    public function addInstitution(\ClassCentral\SiteBundle\Entity\Institution $institutions)
    {
        $this->institutions[] = $institutions;
    }

    /**
     * Get institutions
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInstitutions()
    {
        return $this->institutions;
    }

    /**
     * Add secondary subject
     *
     * @param ClassCentral\SiteBundle\Entity\Stream $subjects
     */
    public function addSubject(\ClassCentral\SiteBundle\Entity\Stream $subject)
    {
        $this->subjects[] = $subject;
    }

    /**
     * Get subjects
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSubjects()
    {
        return $this->subjects;
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

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($desc) {
        $this->description = $desc;
    }

    public function setShortName($shortName) {
        $this->shortName = $shortName;
    }

    public function getShortName() {
        return $this->shortName;
    }

    public function getSearchDesc() {
        return $this->searchDesc;
    }
    public function setSearchDesc($desc) {
        $this->searchDesc = $desc;
    }


    /**
     * Set oneliner
     *
     * @param string $oneliner
     * @return Course
     */
    public function setOneliner($oneliner)
    {
        $this->oneliner = $oneliner;

        return $this;
    }

    /**
     * Get oneliner
     *
     * @return string
     */
    public function getOneliner()
    {
        return $this->oneliner;
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return Course
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }


    /**
     * http://stackoverflow.com/questions/7568231/php-remove-url-not-allowed-characters-from-string
     * @return mixed
     */
    public function getSlug(){
        setlocale(LC_ALL, 'en_US.UTF8'); // for iconv to work properly
        $initiative = '';
        if($this->getInitiative() != null ) {
            $initiative = $this->getInitiative()->getName();
        }
        $url = preg_replace('~[^\\pL0-9_]+~u', '-', $initiative . ' ' . $this->getName());
        $url = trim($url, "-");
        $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
        $url = strtolower($url);
        $url = preg_replace('~[^-a-z0-9_]+~', '', $url);

        return $url;
    }

    /**
     * Get the next offering for this course
     */
    public function getNextOffering()
    {
        /**
         * Filter out offerings which are not available
         */

        $this->getOfferings()->filter(
            function($offering)
            {
                return $offering->getStatus() == Offering::COURSE_NA;
            }
        );

        $offerings = $this->getOfferings();
        if($offerings->isEmpty())
        {
            //TODO: Handle it correctly
            // Create a offering
            $offering = new Offering();
            $offering->setCourse($this);
            $offering->setId(-1);
            $offering->setUrl($this->getUrl());
            $dt = new \DateTime();
            $dt->add(new \DateInterval("P1Y"));
            $offering->setStartDate($dt);
            $offering->setStatus(Offering::START_DATES_UNKNOWN);

            return $offering;
        }

        $nextOffering = $offerings->first();
        $now = new \DateTime();
        $upcoming = array();
        foreach($offerings as $offering)
        {
            if($offering->getStartDate() > $now)
            {
                $upcoming[] = $offering;
            }

            if($offering->getStartDate() > $nextOffering->getStartDate())
            {
                $nextOffering = $offering;
            }
        }

        if( count($upcoming) > 1 )
        {
            // Multiple upcoming. Pick the earliest one
            $nextOffering = array_pop($upcoming);
            foreach($upcoming as $offering)
            {
                if($offering->getStartDate() < $nextOffering->getStartDate())
                {
                    $nextOffering = $offering;
                }
            }
        }



        return $nextOffering;
    }

    public function __toString() {
        return $this->getName();
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
     * Add tags
     *
     * @param \ClassCentral\SiteBundle\Entity\Tag $tags
     * @return Course
     */
    public function addTag(\ClassCentral\SiteBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \ClassCentral\SiteBundle\Entity\Tag $tags
     */
    public function removeTag(\ClassCentral\SiteBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }



    /**
     * Set longDescription
     *
     * @param string $longDescription
     * @return Course
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * Get longDescription
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * Set syllabus
     *
     * @param string $syllabus
     * @return Course
     */
    public function setSyllabus($syllabus)
    {
        $this->syllabus = $syllabus;

        return $this;
    }

    /**
     * Get syllabus
     *
     * @return string
     */
    public function getSyllabus()
    {
        return $this->syllabus;
    }

    /**
     * Set certificate
     *
     * @param boolean $certificate
     * @return Course
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * Get certificate
     *
     * @return boolean
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * Set verifiedCertificate
     *
     * @param boolean $verifiedCertificate
     * @return Course
     */
    public function setVerifiedCertificate($verifiedCertificate)
    {
        $this->verifiedCertificate = $verifiedCertificate;

        return $this;
    }

    /**
     * Get verifiedCertificate
     *
     * @return boolean
     */
    public function getVerifiedCertificate()
    {
        return $this->verifiedCertificate;
    }

    /**
     * Set workloadMin
     *
     * @param integer $workloadMin
     * @return Course
     */
    public function setWorkloadMin($workloadMin)
    {
        $this->workloadMin = $workloadMin;

        return $this;
    }

    /**
     * Get workloadMin
     *
     * @return integer
     */
    public function getWorkloadMin()
    {
        return $this->workloadMin;
    }

    /**
     * Set workloadMax
     *
     * @param integer $workloadMax
     * @return Course
     */
    public function setWorkloadMax($workloadMax)
    {
        $this->workloadMax = $workloadMax;

        return $this;
    }

    /**
     * Get workloadMax
     *
     * @return integer
     */
    public function getWorkloadMax()
    {
        return $this->workloadMax;
    }

    /**
     * Set duplicateCourse
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $duplicateCourse
     * @return Course
     */
    public function setDuplicateCourse(\ClassCentral\SiteBundle\Entity\Course $duplicateCourse = null)
    {
        $this->duplicateCourse = $duplicateCourse;

        return $this;
    }

    /**
     * Get duplicateCourse
     *
     * @return \ClassCentral\SiteBundle\Entity\Course
     */
    public function getDuplicateCourse()
    {
        return $this->duplicateCourse;
    }

    /**
     * Set indepthReview
     *
     * @param \ClassCentral\SiteBundle\Entity\IndepthReview $indepthReview
     * @return Course
     */
    public function setIndepthReview(\ClassCentral\SiteBundle\Entity\IndepthReview $indepthReview = null)
    {
        $this->indepthReview = $indepthReview;

        return $this;
    }

    /**
     * Get indepthReview
     *
     * @return \ClassCentral\SiteBundle\Entity\IndepthReview
     */
    public function getIndepthReview()
    {
        return $this->indepthReview;
    }


    /**
     * Set interview
     *
     * @param \ClassCentral\SiteBundle\Entity\Interview $interview
     * @return Course
     */
    public function setInterview(\ClassCentral\SiteBundle\Entity\Interview $interview = null)
    {
        $this->interview = $interview;

        return $this;
    }

    /**
     * Get interview
     *
     * @return \ClassCentral\SiteBundle\Entity\Interview
     */
    public function getInterview()
    {
        return $this->interview;
    }

    /**
     * Remove offerings
     *
     * @param \ClassCentral\SiteBundle\Entity\Offering $offerings
     */
    public function removeOffering(\ClassCentral\SiteBundle\Entity\Offering $offerings)
    {
        $this->offerings->removeElement($offerings);
    }

    /**
     * Remove institutions
     *
     * @param \ClassCentral\SiteBundle\Entity\Institution $institutions
     */
    public function removeInstitution(\ClassCentral\SiteBundle\Entity\Institution $institutions)
    {
        $this->institutions->removeElement($institutions);
    }

    /**
     * Remove instructors
     *
     * @param \ClassCentral\SiteBundle\Entity\Instructor $instructors
     */
    public function removeInstructor(\ClassCentral\SiteBundle\Entity\Instructor $instructors)
    {
        $this->instructors->removeElement($instructors);
    }

    /**
     * Add credentials
     *
     * @param \ClassCentral\CredentialBundle\Entity\Credential $credentials
     * @return Course
     */
    public function addCredential(Credential $credentials)
    {
        $this->credentials[] = $credentials;

        return $this;
    }

    /**
     * Remove credentials
     *
     * @param \ClassCentral\CredentialBundle\Entity\Credential $credentials
     */
    public function removeCredential(Credential $credentials)
    {
        $this->credentials->removeElement($credentials);
    }

    /**
     * Get credentials
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Add careers
     *
     * @param \ClassCentral\SiteBundle\Entity\Career $careers
     * @return Course
     */
    public function addCareer(\ClassCentral\SiteBundle\Entity\Career $careers)
    {
        $this->careers[] = $careers;

        return $this;
    }

    /**
     * Remove careers
     *
     * @param \ClassCentral\SiteBundle\Entity\Career $careers
     */
    public function removeCareer(\ClassCentral\SiteBundle\Entity\Career $careers)
    {
        $this->careers->removeElement($careers);
    }

    /**
     * Get careers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCareers()
    {
        return $this->careers;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return Course
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set pricePeriod
     *
     * @param string $pricePeriod
     * @return Course
     */
    public function setPricePeriod($pricePeriod)
    {
        $this->pricePeriod = $pricePeriod;

        return $this;
    }

    /**
     * Get pricePeriod
     *
     * @return string
     */
    public function getPricePeriod()
    {
        return $this->pricePeriod;
    }

    /**
     * Set certificatePrice
     *
     * @param integer $certificatePrice
     * @return Course
     */
    public function setCertificatePrice($certificatePrice)
    {
        $this->certificatePrice = $certificatePrice;

        return $this;
    }

    /**
     * Get certificatePrice
     *
     * @return integer
     */
    public function getCertificatePrice()
    {
        return $this->certificatePrice;
    }

    /**
     * Set workloadType
     *
     * @param string $workloadType
     * @return Course
     */
    public function setWorkloadType($workloadType)
    {
        $this->workloadType = $workloadType;

        return $this;
    }

    /**
     * Get workloadType
     *
     * @return string
     */
    public function getWorkloadType()
    {
        return $this->workloadType;
    }

    /**
     * Set durationMin
     *
     * @param integer $durationMin
     * @return Course
     */
    public function setDurationMin($durationMin)
    {
        $this->durationMin = $durationMin;

        return $this;
    }

    /**
     * Get durationMin
     *
     * @return integer
     */
    public function getDurationMin()
    {
        return $this->durationMin;
    }

    /**
     * Set durationMax
     *
     * @param integer $durationMax
     * @return Course
     */
    public function setDurationMax($durationMax)
    {
        $this->durationMax = $durationMax;

        return $this;
    }

    /**
     * Get durationMax
     *
     * @return integer
     */
    public function getDurationMax()
    {
        return $this->durationMax;
    }

    /**
     * Set durationType
     *
     * @param string $durationType
     * @return Course
     */
    public function setDurationType($durationType)
    {
        $this->durationType = $durationType;

        return $this;
    }

    /**
     * Get durationType
     *
     * @return string
     */
    public function getDurationType()
    {
        return $this->durationType;
    }

    public function isPaid()
    {
        return $this->getPrice() != 0;
    }


    /**
     * Set isMooc
     *
     * @param string $isMooc
     * @return Course
     */
    public function setIsMooc($isMooc)
    {
        $this->isMooc = $isMooc;

        return $this;
    }

    /**
     * Get isMooc
     *
     * @return string
     */
    public function getIsMooc()
    {
        return $this->isMooc;
    }

    public function getFormatter()
    {
        return new DefaultCourseFormatter($this);
    }

    public function isCourseNew()
    {
        $newCourse = false;
        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->sub(new \DateInterval("P30D"));
        // Check if its a new course - offered for the first time or added recently
        if($this->getCreated() >= $oneMonthAgo)
        {
            $newCourse = true;
        }

        $offering = $this->getNextOffering();
        if(count($this->getOfferings()) == 1 and $offering->getCreated() > $oneMonthAgo  )
        {
            $newCourse = true;
        }
        if(count($this->getOfferings()) == 1 and $offering->getStatus() != Offering::COURSE_OPEN )
        {
            $newCourse = true;
        }

        return $newCourse;

    }

}

/**
 * Represents the different statuses a course can be
 * Any status 100 or above does not make it the
 * Class CourseStatus
 * @package ClassCentral\SiteBundle\Entity
 */
abstract class CourseStatus
{
    private final function  __construct(){}

    // Any course above this status will not be shown to the user
    const COURSE_NOT_SHOWN_LOWER_BOUND = 100;

    // Statuses
    const AVAILABLE = 0;
    const NOT_AVAILABLE = 100;
    const TO_BE_REVIEWED = 101; // To be reviewed by someone before it is displayed
    const HIDDEN = 102; // Hidden courses are hidden from search and discovery experience. i.e Capstone project
    const UNLISTED = 103; // Unlisted courses are not shown in the discovery experience, but show up when they are searched for.

    public static function getStatuses()
    {
        return array(
            self::AVAILABLE => 'Available',
            self::NOT_AVAILABLE => 'Not Available',
            self::TO_BE_REVIEWED => 'To Be Reviewed',
            self::UNLISTED => 'Unlisted',
            self::HIDDEN => 'Hidden'
        );
    }
}