<?php

namespace ClassCentral\SiteBundle\Command\DataMigration;

use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Entity\UserPreference;

/**
 * Class Version5
 * Migrate mooc_tracker_courses to users_courses_table
 * @package ClassCentral\SiteBundle\Command\DataMigration
 */
class Version5 extends VersionAbstractInterface{

    public function migrate()
    {
        $this->output->writeln("Starting data migration version 5");

        $this->output->writeln('Migrating MOOC tracker courses');
        $em = $this->container->get('Doctrine')->getManager();
        $moocTrackerCourses = $em->getRepository('ClassCentralSiteBundle:MoocTrackerCourse')->findAll();

        foreach($moocTrackerCourses as $mtc)
        {
            $uc = new UserCourse();
            $uc->setUser($mtc->getUser());
            $uc->setCourse($mtc->getCourse());
            $uc->setCreated($mtc->getCreated());
            $uc->setListId(UserCourse::LIST_TYPE_INTERESTED);
            $em->persist($uc);
        }

        $em->flush();

        $this->output->writeln('Creating MOOC Tracker preferences');
        // Creating user preferences for mooc tracker courses and search terms
        $users = $em->getRepository('ClassCentralSiteBundle:User')->findAll();
        foreach($users as $user)
        {
            $upCourses = new UserPreference();
            $upCourses->setUser($user);
            $upCourses->setType(UserPreference::USER_PREFERENCE_MOOC_TRACKER_COURSES);
            $upCourses->setValue("1");

            $upSearchTerms = new UserPreference();
            $upSearchTerms->setUser($user);
            $upSearchTerms->setType(UserPreference::USER_PREFERENCE_MOOC_TRACKER_SEARCH_TERM);
            $upSearchTerms->setValue("1");

            $em->persist($upCourses);
            $em->persist($upSearchTerms);
        }

        $em->flush();

    }
}