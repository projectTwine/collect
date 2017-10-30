<?php

namespace ClassCentral\SiteBundle\Entity;

use ClassCentral\CredentialBundle\Entity\Credential;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClassCentral\SiteBundle\Entity\Institution
 */
class Institution
{
    public static $INS_SHORT_ALIAS = [
        'stanford' => 'Stanford',
        'mit' => 'MIT',
        'harvard'=>'Harvard',
        'gatech' => 'Gerogia Tech',
        'tsu' => 'Tsinghua',
        'iimb' => 'IIM Banglore',
        'delft' => 'Delft',
        'ubc' => 'UBC',
        'umich' => 'Michigan'
    ];

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var text $url
     */
    private $url;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var boolean $isUniversity
     */
    private $isUniversity;

    /**
     * @var ClassCentral\SiteBundle\Entity\Course
     */
    private $courses;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var string $imageUrl
     */
    private $imageUrl;


    /**
     * Number of courses for this institutions
     * @var Int
     */
    private $count;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $credentials;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $continent;


    public function __construct()
    {
        $this->courses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->credentials = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set url
     *
     * @param text $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return text 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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

    /**
     * Set isUniversity
     *
     * @param boolean $isUniversity
     */
    public function setIsUniversity($isUniversity)
    {
        $this->isUniversity = $isUniversity;
    }

    /**
     * Get isUniversity
     *
     * @return boolean 
     */
    public function getIsUniversity()
    {
        return $this->isUniversity;
    }

    /**
     * Add courses
     *
     * @param ClassCentral\SiteBundle\Entity\Course $courses
     */
    public function addCourse(\ClassCentral\SiteBundle\Entity\Course $courses)
    {
        $this->courses[] = $courses;
    }

    /**
     * Get courses
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCourses()
    {
        return $this->courses;
    }
    
    public function __toString() {
        return $this->getName();
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
     * i.e institutions/stanford.jpg
     */
    public function getImageDir()
    {
        return "institutions";
    }

    /**
     * @param Int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return Int
     */
    public function getCount()
    {
        return $this->count;
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
     * Add credentials
     *
     * @param \ClassCentral\SiteBundle\Entity\Credential $credentials
     * @return Institution
     */
    public function addCredential(Credential $credentials)
    {
        $this->credentials[] = $credentials;
    
        return $this;
    }

    /**
     * Remove credentials
     *
     * @param \ClassCentral\SiteBundle\Entity\Credential $credentials
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
     * Set country
     *
     * @param string $country
     * @return Institution
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set continent
     *
     * @param string $continent
     * @return Institution
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;
    
        return $this;
    }

    /**
     * Get continent
     *
     * @return string 
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Returns a short name instead of the whole name
     */
    public function getShortAlias()
    {
        if(isset(self::$INS_SHORT_ALIAS[$this->getSlug()]))
        {
            return self::$INS_SHORT_ALIAS[$this->getSlug()];
        }

        return $this->getName();
    }
}