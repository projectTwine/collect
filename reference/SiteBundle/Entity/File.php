<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * File
 */
class File
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileType;

    /**
     * The user of the Kuber API can set this key. This can be used to detect if a file
     * has been changed.
     * @var string
     */
    private $uniqueKey;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

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
     * Set entity
     *
     * @param string $entity
     * @return File
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    
        return $this;
    }

    /**
     * Get entity
     *
     * @return string 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return File
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set entityId
     *
     * @param string $entityId
     * @return File
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    
        return $this;
    }

    /**
     * Get entityId
     *
     * @return string 
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return File
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileType
     *
     * @param string $fileType
     * @return File
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
    
        return $this;
    }

    /**
     * Get fileType
     *
     * @return string 
     */
    public function getFileType()
    {
        return $this->fileType;
    }



    /**
     * Set key
     *
     * @param string $key
     * @return File
     */
    public function setUniqueKey($key)
    {
        $this->uniqueKey = $key;
    
        return $this;
    }

    /**
     * Get key
     *
     * @return string 
     */
    public function getUniqueKey()
    {
        return $this->uniqueKey;
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