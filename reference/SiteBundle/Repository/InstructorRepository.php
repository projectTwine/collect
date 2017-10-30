<?php

namespace ClassCentral\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;

class InstructorRepository extends EntityRepository {

    public function getInstructorsByOffering($offeringIds = array())
    {

        $em = $this->getEntityManager();

        $offeringIdsString = implode("','", $offeringIds);
        $result = $em->createQuery(
                        "SELECT i.name, o.id FROM ClassCentralSiteBundle:Instructor i JOIN  
                         i.offerings o WHERE o.id in ('{$offeringIdsString}')")
                ->getArrayResult();

        $instructors = array();
        foreach ($result as $instructor)
        {
            $instructors[$instructor['id']][] = $instructor['name'];
        }
        
        return $instructors;
    }

}

?>
