<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 */
class Category
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $careers;


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
     * Set name
     *
     * @param string $name
     * @return Category
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
     * Set slug
     *
     * @param string $slug
     * @return Category
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->careers = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add careers
     *
     * @param \ClassCentral\SiteBundle\Entity\Career $careers
     * @return Category
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
}