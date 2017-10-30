<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/10/15
 * Time: 8:24 PM
 */

namespace ClassCentral\SiteBundle\Command\DataMigration;
use ClassCentral\SiteBundle\Entity\UserPreference;

/**
 * Adds a preference for review solicitation emails
 * Class Version9
 * @package ClassCentral\SiteBundle\Command\DataMigration
 */
class Version9 extends VersionAbstractInterface{

    public function migrate()
    {
        $this->output->writeln("Starting data migration version 9");

        $em = $this->container->get('Doctrine')->getManager();
        $users = $em->getRepository('ClassCentralSiteBundle:User')->findAll();
        foreach($users as $user)
        {
            $up = new UserPreference();
            $up->setUser( $user );
            $up->setType( UserPreference::USER_PREFERENCE_REVIEW_SOLICITATION );
            $up->setValue("1");
            $em->persist( $up );
        }

        $em->flush();
    }
}