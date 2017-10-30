<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/22/14
 * Time: 1:20 AM
 */

namespace ClassCentral\SiteBundle\Repository;


use ClassCentral\SiteBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class UserRepository extends EntityRepository {

    public function getReviewUser()
    {
        return $this->findOneBy( array('email' => User::REVIEW_USER_EMAIL) );
    }

    public function getUsers($userIds = array())
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query
            ->add('select', 'u.id as id, u.name as name, u.handle as handle, p.location as location, p.aboutMe as aboutMe')
            ->add('from', 'ClassCentralSiteBundle:User u')
            ->leftJoin('u.profile','p')
            ->andWhere('u.isPrivate = 0 and u.id in (:userIds)')
            ->orderBy('p.score','DESC')
            ->setParameter('userIds', $userIds)
        ;
        return $query->getQuery()->getResult( Query::HYDRATE_ARRAY );
    }
} 