<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserCourse
 */
class UserCourse
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $created;

    /**
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

    const LIST_TYPE_INTERESTED = 1;
    const LIST_TYPE_ENROLLED = 2;
    const LIST_TYPE_COMPLETED = 3;
    const LIST_TYPE_AUDITED = 4;
    const LIST_TYPE_PARTIALLY_COMPLETED = 5;
    const LIST_TYPE_DROPPED = 6;
    const LIST_TYPE_CURRENT = 7;

    public static $lists = array(
        self::LIST_TYPE_INTERESTED => array('slug' => 'interested','desc' => "Interested"),
        self::LIST_TYPE_ENROLLED => array('slug'=>'enrolled','desc'=>'Enrolled'),
        self::LIST_TYPE_CURRENT => array('slug' => 'current', 'desc' => 'Taking right now' ),
        self::LIST_TYPE_PARTIALLY_COMPLETED => array('slug' => 'partially_completed','desc' => 'Partially Completed'),
        self::LIST_TYPE_COMPLETED => array('slug'=>'completed','desc'=>'Completed'),
        self::LIST_TYPE_AUDITED => array('slug'=>'audited','desc'=>'Audited'),
        self::LIST_TYPE_DROPPED => array('slug' => 'dropped','desc' => 'Dropped'),
    );

    public static $transcriptList = array(
        self::LIST_TYPE_CURRENT => array('slug' => 'current', 'desc' => 'Taking right now' ),
        self::LIST_TYPE_COMPLETED => array('slug'=>'completed','desc'=>'Completed'),
        self::LIST_TYPE_PARTIALLY_COMPLETED => array('slug' => 'partially_completed','desc' => 'Partially Completed'),
        self::LIST_TYPE_AUDITED => array('slug'=>'audited','desc'=>'Audited'),
        self::LIST_TYPE_ENROLLED => array('slug'=>'enrolled','desc'=>'Enrolled'),
        self::LIST_TYPE_DROPPED => array('slug' => 'dropped','desc' => 'Dropped'),
    );


    public static $progress = array(
        self::LIST_TYPE_CURRENT => array('slug' => 'current', 'desc' => 'Taking right now' ),
        self::LIST_TYPE_PARTIALLY_COMPLETED => array('slug'=>'partially_completed','desc' => 'Partially Completed'),
        self::LIST_TYPE_COMPLETED => array('slug'=>'completed','desc'=>'Completed'),
        self::LIST_TYPE_AUDITED => array('slug'=>'audited','desc'=>'Audited'),
        self::LIST_TYPE_DROPPED => array('slug'=>'dropped','desc'=>'Dropped'),
    );



    public static function getListTypes()
    {
        $types = array();
        foreach(self::$lists as $list)
        {
            $types[] = $list['slug'];
        }
        return $types;
    }


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
     * Set created
     *
     * @param \DateTime $created
     * @return UserCourse
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
     * Set listId
     *
     * @param integer $listId
     * @return UserCourse
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

    public function getList()
    {
        if($this->getListId())
        {
            return self::$lists[$this->getListId()];
        }

        return null;
    }


    /**
     * Set user
     *
     * @param \ClassCentral\SiteBundle\Entity\User $user
     * @return UserCourse
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
     * @return UserCourse
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
     * @return UserCourse
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
}
