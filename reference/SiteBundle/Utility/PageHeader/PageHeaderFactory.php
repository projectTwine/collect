<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 11/26/13
 * Time: 4:19 PM
 */

namespace ClassCentral\SiteBundle\Utility\PageHeader;


use ClassCentral\SiteBundle\Entity\Career;
use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Institution;
use ClassCentral\SiteBundle\Entity\Language;
use ClassCentral\SiteBundle\Entity\Stream;

class PageHeaderFactory {

    public static function  get($entity)
    {
        if($entity instanceof Initiative)
        {
            return self::getFromInitiative($entity);
        }

        if($entity instanceof Stream)
        {
            return self::getFromStream($entity);
        }

        if($entity instanceof Institution)
        {
            return self::getFromInstitution($entity);
        }

        if($entity instanceof Language)
        {
            return self::getFromLanguage($entity);
        }

        if($entity instanceof Career)
        {
            return self::getFromCareer($entity);
        }

        // Should not reach here
        throw new \Exception('$entity should be a type of Initiative, Stream, Institution, Language');
    }

    private static function getFromInitiative(Initiative $entity)
    {
        $info = new PageHeaderInfo("Initiative");
        $info->setName($entity->getName());
        $info->setUrl($entity->getUrl());
        $info->setDescription($entity->getDescription());
        if($entity->getImageUrl())
        {
            $info->setImageUrl($entity->getImageDir().'/' . $entity->getImageUrl());
        }
        return $info;
    }

    private static function getFromStream(Stream $entity)
    {
        $info = new PageHeaderInfo("Stream");
        $info->setName($entity->getName() . ' | Free Online Courses');
        $info->setDescription(
            "Study free online <em>{$entity->getName()}</em> courses & MOOCs from top universities and colleges. Read reviews to decide if a class is right for you. 
                Learn the fundamentals of <em>{$entity->getName()}</em> and increase your career prospects by earning certificates of completion.
            "
        );
        if($entity->getImageUrl())
        {
            $info->setImageUrl($entity->getImageDir().'/' . $entity->getImageUrl());
        }
        return $info;
    }

    private static function getFromInstitution(Institution $entity)
    {
        $free = 'Free';
        if($entity->getSlug() == 'keiser')
        {
            $free = '';
        }
        $info = new PageHeaderInfo("Institution");
        $info->setName($entity->getName().  " | $free Online Courses");
        $info->setUrl($entity->getUrl());
        $info->setDescription($entity->getDescription());
        if($entity->getImageUrl())
        {
            $info->setImageUrl($entity->getImageDir(). '/' . $entity->getImageUrl());
        }
        return $info;
    }

    private static function getFromLanguage(Language $entity)
    {
        $info = new PageHeaderInfo("Language");
        $info->setName($entity->getName() . " Language");

        return $info;
    }

    private static function getFromCareer(Career $career)
    {
        $info = new PageHeaderInfo("Career");
        $info->setName($career->getName() . ' Online Courses');

        return $info;
    }

} 