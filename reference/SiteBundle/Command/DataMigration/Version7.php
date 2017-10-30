<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/22/14
 * Time: 1:13 AM
 */

namespace ClassCentral\SiteBundle\Command\DataMigration;
use ClassCentral\SiteBundle\Entity\User;

/**
 * Create a review user for anonymous users
 * Class Version7
 * @package ClassCentral\SiteBundle\Command\DataMigration
 */
class Version7 extends VersionAbstractInterface{

    public function migrate()
    {
        $em = $this->container->get('Doctrine')->getManager();

        $u = new User();
        $u->setName('Anonymous');
        $u->setEmail( User::REVIEW_USER_EMAIL );
        $u->setPassword('NEVERLOGSIN' .$this->generateRandomString() );

        $em->persist( $u );
        $em->flush();
    }

    private function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}