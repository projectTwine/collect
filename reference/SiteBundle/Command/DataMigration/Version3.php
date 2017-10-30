<?php

namespace ClassCentral\SiteBundle\Command\DataMigration;

/**
 * Class Version3
 * Migration to add a short_name to coursera courses
 * @package ClassCentral\SiteBundle\Command\DataMigration
 */
class Version3 extends VersionAbstractInterface {

    const COURSES_JSON = 'https://www.coursera.org/maestro/api/topic/list?full=1';

    public function migrate()
    {
        $this->output->writeln("Starting data migration version 3");
        $this->output->writeln("Adding short_name to coursera courses");
        $courseraCourses = $this->getCoursesArray();
        foreach($courseraCourses as $courseraCourse)
        {
            $em = $this->container->get('Doctrine')->getManager();

            $course = $em->getRepository('ClassCentralSiteBundle:Course')
                         ->findOneBy(array('name' => $courseraCourse['name']));
            if(!$course)
            {
                $this->output->writeln("Course {$courseraCourse['name']} not found");
                continue;
            }

            // Add a prefix coursera_ to namespace it
            $course->setShortName('coursera_' . $courseraCourse['short_name']);

            $em->persist($course);
        }

        $em->flush();
    }

    private function getCoursesArray()
    {
        $this->output->writeln("Getting the coursera json");
        return json_decode(file_get_contents(self::COURSES_JSON), true);
    }
}