<?php

namespace ClassCentral\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Email
 */
class Email
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $newsletters;

    /**
     * @var boolean
     */
    private $isverified;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->newsletters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isverified = 0;
        $this->created = new \DateTime();
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
     * Set email
     *
     * @param string $email
     * @return Email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Email
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
     * Add newsletters
     *
     * @param \ClassCentral\SiteBundle\Entity\Newsletter $newsletters
     * @return Email
     */
    public function addNewsletter(\ClassCentral\SiteBundle\Entity\Newsletter $newsletters)
    {
        $this->newsletters[] = $newsletters;
    
        return $this;
    }

    /**
     * Remove newsletters
     *
     * @param \ClassCentral\SiteBundle\Entity\Newsletter $newsletters
     */
    public function removeNewsletter(\ClassCentral\SiteBundle\Entity\Newsletter $newsletters)
    {
        $this->newsletters->removeElement($newsletters);
    }

    /**
     * Get newsletters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNewsletters()
    {
        return $this->newsletters;
    }

    /**
     * Signs up for a newsletter
     */
    public function subscribe(\ClassCentral\SiteBundle\Entity\Newsletter $newsletter)
    {
        $signedUp = false;
        foreach($this->getNewsletters() as $ns)
        {
            if($ns->getCode() == $newsletter->getCode())
            {
                $signedUp = true;
                break;
            }
        }
        if(!$signedUp)
        {
            $this->addNewsletter($newsletter);
        }
    }


    /**
     * Set isverified
     *
     * @param boolean $isverified
     * @return Email
     */
    public function setIsverified($isverified)
    {
        $this->isverified = $isverified;
    
        return $this;
    }

    /**
     * Get isverified
     *
     * @return boolean 
     */
    public function getIsverified()
    {
        return $this->isverified;
    }
}