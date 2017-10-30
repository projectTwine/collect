<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 7:12 PM
 */

namespace ClassCentral\SiteBundle\Repository;

use ClassCentral\SiteBundle\Entity\Institution;
use Doctrine\ORM\EntityRepository;

class InstitutionRepository extends EntityRepository {

    /**
     * Given an institution entity it reurns the course count
     * for that institution
     * @param Institution $ins
     */
    public function getCourseCountByInstitution(Institution $ins)
    {
        $courses = $ins->getCourses();
        $courseCount = 0;
        foreach($courses as $course)
        {
            if($course->getStatus() < 100)
            {
                $courseCount++;
            }
        }
        return $courseCount;
    }
} 