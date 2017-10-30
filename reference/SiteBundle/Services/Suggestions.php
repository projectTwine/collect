<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/15/16
 * Time: 4:08 PM
 */

namespace ClassCentral\SiteBundle\Services;


use ClassCentral\SiteBundle\Entity\Item;
use ClassCentral\SiteBundle\Entity\User as UserEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Suggestions
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * Find all the new courses based on user's follows added between now and daysgo
     * @param User $user
     * @param $startDate
     * @param $endDate
     */
    public function newCoursesbyUser(UserEntity $user, $daysAgo)
    {
        // Get follows
        $cl = $this->container->get('course_listing');

        $follows = $user->getFollowsCategorizedByItem();

        $must = array(
            'range' => array(
                'created' => array(
                    "gte" => "now-{$daysAgo}d/d",
                    "lt" => "now/d"
                )
            ));

        // Set the page as -1 to get all courses
        $data = $cl->byFollows($follows, array('page' => -1), $must,$this->getMustNot($user));

        return $data;

    }

    /**
     * Find all the course recommendations based on user's follows between the two dates
     * @param User $user
     * @param $startDate
     * @param $endDate
     */
    public function byStartDate(UserEntity $user, $startDate, $endDate)
    {
        // Get follows
        $cl = $this->container->get('course_listing');

        $follows = $user->getFollowsCategorizedByItem();

        $must = array(
            'range' => array(
                'nextSession.startDate' => array(
                    "gte" => $startDate->format('Y-m-d'),
                    "lte" => $endDate->format('Y-m-d'),
                )
            ));

        $data = $cl->byFollows($follows, array(), $must,$this->getMustNot($user));

        return $data;
    }

    /**
     * Gets the recommended courses for the personalized recommendations
     * page for the user
     * @param User $user
     * @param $params
     */
    public function getRecommendations(UserEntity $user, $params)
    {
        $cl = $this->container->get('course_listing');

        $follows = $user->getFollowsCategorizedByItem();

        $must =  array(
            'terms' => array(
                'subjects.id' => $follows[Item::ITEM_TYPE_SUBJECT]
        ));


        $data = $cl->byFollows($follows, $params, $must, $this->getMustNot($user));

        return $data;
    }

    public function meetYourNextCourse($params)
    {
        $cl = $this->container->get('course_listing');
        $userSession = $this->container->get('user_session');
        $follows = $userSession->getNextCourseFollows();
        $must =  array(
            'terms' => array(
                'subjects.id' => $follows[Item::ITEM_TYPE_SUBJECT]
            ));

        $data = $cl->byFollows($follows, $params, $must, array());

        return $data;
    }

    private function getMustNot(UserEntity $user)
    {
        $mustNot = array();
        $courseIds= $user->getUserCourseIds();
        // Limit the course Ids to prevent ES from breaking.
        $courseIds = array_slice( $courseIds,0,100  );

        if( !empty($courseIds) )
        {
            $mustNot =  array(
                'terms' => array(
                    'course.id' => $courseIds
                ));
        }

        return $mustNot;
    }

}