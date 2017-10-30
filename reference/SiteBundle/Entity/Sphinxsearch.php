<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\Sphinxsearch
 */
class Sphinxsearch
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var text $instructors
     */
    private $instructors;

    /**
     * @var text $description
     */
    private $description;

    /**
     * @var string $initiative
     */
    private $initiative;

    /**
     * @var string $stream
     */
    private $stream;

    /**
     * @var text $search
     */
    private $search;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
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

    /**
     * Set instructors
     *
     * @param text $instructors
     */
    public function setInstructors($instructors)
    {
        $this->instructors = $instructors;
    }

    /**
     * Get instructors
     *
     * @return text 
     */
    public function getInstructors()
    {
        return $this->instructors;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set initiative
     *
     * @param string $initiative
     */
    public function setInitiative($initiative)
    {
        $this->initiative = $initiative;
    }

    /**
     * Get initiative
     *
     * @return string 
     */
    public function getInitiative()
    {
        return $this->initiative;
    }

    /**
     * Set stream
     *
     * @param string $stream
     */
    public function setStream($stream)
    {
        $this->stream = $stream;
    }

    /**
     * Get stream
     *
     * @return string 
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Set search
     *
     * @param text $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * Get search
     *
     * @return text 
     */
    public function getSearch()
    {
        return $this->search;
    }
}