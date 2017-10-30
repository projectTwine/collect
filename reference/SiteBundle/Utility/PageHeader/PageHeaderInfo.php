<?php

namespace ClassCentral\SiteBundle\Utility\PageHeader;

/**
 * Data from this class is used to generate the header for Stream, Provider, and University pages
 * Class PageHeaderInfo
 * @package ClassCentral\SiteBundle\Utility\PageHeader
 */
class PageHeaderInfo {

    private $name;

    private $url;

    private $description;

    private $imageUrl;

    /**
     * URL of the page being rendered
     * @var string $pageUrl
     */
    private $pageUrl;

    /**
     * The text used to generate twitter share text
     * @var string $tweet
     */
    private $tweet;

    /**
     * Type of header - initiative, stream, institution, career
     * @var
     */
    private $type;

    public function __construct($type)
    {
        $this->setType($type);
    }

    public function getSubject()
    {
        return sprintf("List of %s MOOCs",$this->getName());
    }

    /**
     * @return string
     */
    public function getTweet()
    {
        if(!$this->tweet)
        {
            return sprintf("List of %s free online courses/MOOCs",$this->getName());
        }
        return $this->tweet;
    }

    /**
     * @param string $tweet
     */
    public function setTweet($tweet)
    {
        $this->tweet = $tweet;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getPageUrl()
    {
        return $this->pageUrl;
    }

    /**
     * @param string $pageUrl
     */
    public function setPageUrl($pageUrl)
    {
        $this->pageUrl = $pageUrl;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


} 