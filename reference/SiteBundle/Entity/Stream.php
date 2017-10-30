<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\Stream
 */
class Stream
{
    /**
     * @var integer $id
     */
    private $id;


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
     * @var string $name
     */
    private $name;
    
    private $slug;
    
      /**
     * @var boolean $showInNav
     */
    private $showInNav;
    private $courses = null;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \ClassCentral\SiteBundle\Entity\Stream
     */
    private $parentStream;

    /**
     * @var string $imageUrl
     */
    private $imageUrl;

    /**
     * Color of the box on the subjects page
     * @var string
     */
    private $color;

    /**
     * Color of the subjects chidlren
     * @var string
     */
    private $childColor;

    /**
     * @var integer
     */
    private $displayOrder;

    /*
     * Count of number of courses
     * @var integer
     */
    private $courseCount;


    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }
    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    
    public function __toString() {
        return $this->getName();
    }

    public function getSlug() {
        return $this->slug;
    }
    
    public function setSlug($slug) {
        $this->slug = $slug;
    }
    
    public function getShowInNav(){
        return $this->showInNav;
    }
    
    public function setShowInNav($showInNav){
        $this->showInNav = $showInNav;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Gets the name of the directory on the cdn.
     * i.e subjects/computerscience.jpg
     */
    public function getImageDir()
    {
        return "subjects";
    }


    /**
     * Add courses
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $courses
     * @return Stream
     */
    public function addCourse(\ClassCentral\SiteBundle\Entity\Course $courses)
    {
        $this->courses[] = $courses;
    
        return $this;
    }

    /**
     * Remove courses
     *
     * @param \ClassCentral\SiteBundle\Entity\Course $courses
     */
    public function removeCourse(\ClassCentral\SiteBundle\Entity\Course $courses)
    {
        $this->courses->removeElement($courses);
    }

    /**
     * Add children
     *
     * @param \ClassCentral\SiteBundle\Entity\Stream $children
     * @return Stream
     */
    public function addChildren(\ClassCentral\SiteBundle\Entity\Stream $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \ClassCentral\SiteBundle\Entity\Stream $children
     */
    public function removeChildren(\ClassCentral\SiteBundle\Entity\Stream $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parentStream
     *
     * @param \ClassCentral\SiteBundle\Entity\Stream $parentStream
     * @return Stream
     */
    public function setParentStream(\ClassCentral\SiteBundle\Entity\Stream $parentStream = null)
    {
        $this->parentStream = $parentStream;
    
        return $this;
    }

    /**
     * Get parentStream
     *
     * @return \ClassCentral\SiteBundle\Entity\Stream 
     */
    public function getParentStream()
    {
        return $this->parentStream;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return Stream
     */
    public function setColor($color)
    {
        $this->color = $color;
    
        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set displayOrder
     *
     * @param integer $displayOrder
     * @return Stream
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
    
        return $this;
    }

    /**
     * Get displayOrder
     *
     * @return integer 
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param mixed $courseCount
     */
    public function setCourseCount($courseCount)
    {
        $this->courseCount = $courseCount;
    }

    /**
     * @return mixed
     */
    public function getCourseCount()
    {
        return $this->courseCount;
    }

    /**
     * @return string
     */
    public function getChildColor()
    {
        return $this->childColor;
    }

    /**
     * @param string $childColor
     */
    public function setChildColor($childColor)
    {
        $this->childColor = $childColor;
    }

    public function getArray()
    {
        return array(
            'id' => $this->getId(),
            'slug' => $this->getSlug(),
            'name' => $this->getName(),
            'courseCount' => $this->getCourseCount(),

        );
    }
}