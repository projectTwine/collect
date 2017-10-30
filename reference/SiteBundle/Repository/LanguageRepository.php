<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/16/14
 * Time: 12:05 AM
 */

namespace ClassCentral\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LanguageRepository extends EntityRepository {

    public function getCourseCountByLanguages()
    {
        $em = $this->getEntityManager();
        // $validStatusBound = CourseStatus::COURSE_NOT_SHOWN_LOWER_BOUND;
        $validStatusBound = 100; // Hardcoding because CourseStatus cant be found error
        $results = $em->createQuery(
            "SELECT l.id, l.name, count(DISTINCT c.id) as courseCount
             FROM ClassCentralSiteBundle:Language l
             JOIN l.courses c
             WHERE c.status < $validStatusBound
             GROUP BY c.language
             ORDER BY l.displayOrder ASC
            "
        )->getArrayResult();


        $languages = array();
        foreach($results as $result)
        {
            $languages[$result['id']] = $result;
        }

        return $languages;
    }
} 