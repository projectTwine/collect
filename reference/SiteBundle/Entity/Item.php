<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/31/15
 * Time: 5:47 PM
 */

namespace ClassCentral\SiteBundle\Entity;

use ClassCentral\CredentialBundle\Entity\Credential;

/**
 * Class Item
 * A class to different items at Class Central
 *i.e Credential, Subject, institution
 * @package ClassCentral\SiteBundle\Entity
 */
class Item
{

    private $type;

    private $id;

    const ITEM_TYPE_CREDENTIAL = 'credential';
    const ITEM_TYPE_SUBJECT = 'subject';
    const ITEM_TYPE_INSTITUTION = 'institution';
    const ITEM_TYPE_PROVIDER = 'provider';
    const ITEM_TYPE_TAG = 'tag';
    const ITEM_TYPE_CAREER = 'career';
    const ITEM_TYPE_LANGUAGE = 'language';
    const ITEM_TYPE_COLLECTION = 'collection';
    const ITEM_TYPE_COURSE = 'course';


    public static $items = array(
        self::ITEM_TYPE_CREDENTIAL, self::ITEM_TYPE_SUBJECT, self::ITEM_TYPE_INSTITUTION,
        self::ITEM_TYPE_PROVIDER, self::ITEM_TYPE_TAG, self::ITEM_TYPE_CAREER,
        self::ITEM_TYPE_LANGUAGE,self::ITEM_TYPE_COLLECTION,self::ITEM_TYPE_COURSE
    );

    private function __construct()
    {

    }

    /**
     * @param Item $item
     */
    public static function getItemInfo(Item $item)
    {
        $repository = null;

        switch ($item->getType() )
        {
            case self::ITEM_TYPE_CREDENTIAL:
                $repository = 'ClassCentralCredentialBundle:Credential';
                break;
            case self::ITEM_TYPE_SUBJECT:
                $repository = 'ClassCentralSiteBundle:Stream';
                break;
            case self::ITEM_TYPE_PROVIDER:
                $repository ='ClassCentralSiteBundle:Initiative';
                break;
            case self::ITEM_TYPE_INSTITUTION:
                $repository = 'ClassCentralSiteBundle:Institution';
                break;
            case self::ITEM_TYPE_TAG:
                $repository = 'ClassCentralSiteBundle:Tag';
                break;
            case self::ITEM_TYPE_CAREER:
                $repository = 'ClassCentralSiteBundle:Career';
                break;
            case self::ITEM_TYPE_LANGUAGE:
                $repository = 'ClassCentralSiteBundle:Language';
                break;
            case self::ITEM_TYPE_COLLECTION:
                $repository = 'ClassCentralSiteBundle:Collection';
                break;
            case self::ITEM_TYPE_COURSE:
                $repository = 'ClassCentralSiteBundle:Course';
                break;
            default:
                throw new \Exception("Item does not exist");
        }

        return array(
            'repository' => $repository
        );
    }

    public static function getItemFromObject($obj)
    {
        $item = new Item();
        $item->setId( $obj->getId() );

        switch(true) {
            case $obj instanceof Credential:
                $item->setType(self::ITEM_TYPE_CREDENTIAL);
                break;
            case $obj instanceof Stream:
                $item->setType(self::ITEM_TYPE_SUBJECT);
                break;
            case $obj instanceof Initiative:
                $item->setType(self::ITEM_TYPE_PROVIDER);
                break;
            case $obj instanceof Institution:
                $item->setType(self::ITEM_TYPE_INSTITUTION);
                break;
            case $obj instanceof Tag:
                $item->setType(self::ITEM_TYPE_TAG);
                break;
            case $obj instanceof Career:
                $item->setType(self::ITEM_TYPE_CAREER);
                break;
            case $obj instanceof Language:
                $item->setType(self::ITEM_TYPE_LANGUAGE);
                break;
            case $obj instanceof Collection:
                $item->setType(self::ITEM_TYPE_COLLECTION);
                break;
            case $obj instanceof Course:
                $item->setType(self::ITEM_TYPE_COURSE);
                break;
            default:
                throw new \Exception("Item does not exist");
        }

        return $item;
    }

    public static function getItem($type,$itemId)
    {
        if( in_array($type,self::$items) )
        {
            $item = new Item();
            $item->setType($type);
            $item->setId($itemId);
            return $item;
        }
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



}