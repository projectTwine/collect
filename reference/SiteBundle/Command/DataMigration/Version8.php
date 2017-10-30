<?php

namespace ClassCentral\SiteBundle\Command\DataMigration;

class Version8 extends VersionAbstractInterface {

    // Update FutureLearn courses with uuids for courses and offerings from the
    // API
    public function migrate()
    {
        $em = $this->container->get('Doctrine')->getManager();
        $courseRepository = $em->getRepository('ClassCentralSiteBundle:Course');
        $provider = $em->getRepository('ClassCentralSiteBundle:Initiative')->findOneBy(array(
            'name' => 'FutureLearn'
        ));

        $flCourses = json_decode(file_get_contents( 'https://www.futurelearn.com/feeds/courses' ), true );
        $courseCount = 0;
        foreach ($flCourses as $flCourse)
        {
            $courseCount++;
            $dbCourse = $courseRepository->findOneBy(array(
                'name' =>$flCourse['name'],
                'initiative' => $provider,
            ));

            if($dbCourse)
            {

                // Update the course shortname with uuid
                $dbCourse->setShortName( $flCourse['uuid'] );
                $em->persist( $dbCourse );

                // Update the offering with uuids
                foreach ($dbCourse->getOfferings() as $offering )
                {
                    $runFound = false;
                    foreach ($flCourse['runs'] as $run)
                    {
                        $dt = new \DateTime( $run['start_date'] );
                        if($offering->getStartDate() == $dt )
                        {
                            $runFound = true;
                            $offering->setShortName( $run['uuid'] );
                            $offering->setUrl( $flCourse['url'] );
                            $em->persist( $offering );
                            break;
                        }
                    }

                    if( !$runFound )
                    {
                        echo "Run Not found for course {$flCourse['name']} \n";
                    }
                }
            }
            else
            {
                echo "{$flCourse['name']} not found \n";
            }
        }

        $em->flush();
        echo "Course Count - $courseCount \n";
    }
}