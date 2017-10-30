<?php

namespace ClassCentral\SiteBundle\Command\DataMigration;

use ClassCentral\SiteBundle\Entity\Offering;

/**
 * Class Version2
 * Migrating data from offering level to course level
 * Following things have to be migrated:
 *  - Instructors
 *  - Course URL
 *  - Youtube video
 *  - Search description
 *  - Course Length
 * @package ClassCentral\SiteBundle\Command\DataMigration
 */
class Version2 extends VersionAbstractInterface {

    public function migrate()
    {
        $this->output->writeln("Starting data migration version 2");

        // Get all offerings
        $em = $this->container->get('Doctrine')->getManager();
        $courses = $em->getRepository('ClassCentralSiteBundle:Course')
                   ->findAll();

        foreach($courses as $course)
        {
            //Figure out an offering of $course from which the data needs to be populated

            $offerings = $course->getOfferings();
            $offering = null;

            // Filter out the ones which are not available
            $offerings = $offerings->filter(function(Offering $off){
                return $off->getStatus() != Offering::COURSE_NA;
            });

            // Skip the courses with no offerings
            if(count($offerings) == 0)
            {
                $this->output->writeln($course->getName() . " has no offerings");
                continue;
            }

            if(count($offerings) == 1)
            {
                // Has only one offering. Use this offering
                $offering = $offerings->current();
            } else
            {

                // More than one offering select the latest offering
                $offering = $offerings->current();
                foreach ($offerings as $courseOffering)
                {
                    if ($courseOffering->getStartDate()->getTimestamp() > $offering->getStartDate()->getTimestamp())
                    {
                        $offering = $courseOffering;
                    }

                }
            }


            // Now that we have the offering save the offering details
            $course->setUrl( $offering->getUrl() );
            $course->setLength($offering->getLength());
            $course->setSearchDesc($offering->getSearchDesc());
            $course->setVideoIntro($offering->getVideoIntro());
            foreach($offering->getInstructors() as $instructor)
            {
                $course->addInstructor($instructor);
            }

            $em->persist( $course );

        }

        $em->flush();

    }
}