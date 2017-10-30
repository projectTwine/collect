<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 4/23/16
 * Time: 11:39 PM
 */

namespace ClassCentral\SiteBundle\Command\DataMigration;


use ClassCentral\SiteBundle\Entity\Course;

class Version10 extends VersionAbstractInterface
{
    public function migrate()
    {
        $this->output->writeln("Starting data migration version 10");
        $em = $this->container->get('Doctrine')->getManager();
        $courses = $em->getRepository('ClassCentralSiteBundle:Course')->findAll();
        foreach($courses as $course)
        {
            // Update length
            $course->setDurationMin( $course->getLength() );
            $course->setDurationMax( $course->getLength() );

            // Migrate Certificate
            $course->setCertificate( $course->getCertificate() || $course->getVerifiedCertificate() );

            // Migrate Workload
            $course->setWorkloadType(Course::WORKLOAD_TYPE_HOURS_PER_WEEK);

            $em->persist( $course );
        }

        $em->flush();
    }
}