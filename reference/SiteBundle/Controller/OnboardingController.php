<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 2/15/16
 * Time: 3:02 PM
 */

namespace ClassCentral\SiteBundle\Controller;


use ClassCentral\SiteBundle\Entity\Item;
use ClassCentral\SiteBundle\Entity\Profile;
use ClassCentral\SiteBundle\Services\Filter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class OnboardingController extends Controller
{

    public function stepProfileAction(Request $request)
    {
        $user = $this->getUser();
        $profile = ($user->getProfile()) ? $user->getProfile() : new Profile();

        $html = $this->render('ClassCentralSiteBundle:Onboarding:profile.html.twig',
            array(
                'user' => $user,
                'profile'=> $profile,
                'degrees' => Profile::$degrees,
            ))
            ->getContent();

        $response = array(
            'modal' => $html
        );

        return new Response( json_encode($response) );
    }

    public function stepFollowSubjectsAction(Request $request)
    {
        $user = $this->getUser();
        $userSession = $this->get('user_session');
        $cache = $this->get('cache');

        $subjectsController = new StreamController();
        $subjects = $cache->get('stream_list_count', array($subjectsController, 'getSubjectsList'),array($this->container));

        $childSubjects = array();
        foreach($subjects['parent'] as $parent)
        {
            if( !empty($subjects['children'][$parent['id']]))
            {
                foreach($subjects['children'][$parent['id']] as $child)
                {
                    $childSubjects[] = $child;
                }
            }
        }
        $follows = $userSession->getFollows();

        $html = $this->render('ClassCentralSiteBundle:Onboarding:followsubjects.html.twig',
            array(
                'user' => $user,
                'subjects' => $subjects,
                'childSubjects' => $childSubjects,
                'followSubjectItem' => Item::ITEM_TYPE_SUBJECT
            ))
            ->getContent();

        $response = array(
            'modal' => $html,
        );

        return new Response( json_encode($response) );
    }

    public function stepFollowInstitutionsAction(Request $request)
    {
        $user = $this->getUser();
        $userSession = $this->get('user_session');
        $cache = $this->get('cache');

        $insController = new InstitutionController();
        $insData = $insController->getInstitutions($this->container,true);

        $providerController = new InitiativeController();
        $providersData = $providerController->getProvidersList($this->container);

        $html = $this->render('ClassCentralSiteBundle:Onboarding:followInstitutions.html.twig',
            array(
                'user' => $user,
                'followInstitutionItem' => Item::ITEM_TYPE_INSTITUTION,
                'followProviderItem' => Item::ITEM_TYPE_PROVIDER,
                'institutions' => $insData['institutions'],
                'providers' => $providersData['providers'],
            ))
            ->getContent();

        $response = array(
            'modal' => $html,
        );

        return new Response( json_encode($response) );
    }

    public function stepFollowCoursesAction(Request $request)
    {
        $user = $this->getUser();
        $cl = $this->get('course_listing');
        $finder = $this->container->get('course_finder');


        // Additional upcoming or interested courses to gauge interest:
        $courses = array(7130,7463,7887,8289);
        $interestingCourses = $finder->byCourseIds($courses);


        // Top 250 Courses
        // Find the top 250 courses.
        $sort = array();
        $sort[] = array(
            'ratingSort' => array(
                'order' => 'desc'
            )
        );
        $filters = array(
            'session' => 'upcoming,selfpaced,recent'
        );

        $results = $finder->byLanguage( 'english', Filter::getQueryFilters( $filters), $sort,-1 );

        $html = $this->render('ClassCentralSiteBundle:Onboarding:followCourses.html.twig',
            array(
                'user' => $user,
                'followCourseItem' => Item::ITEM_TYPE_COURSE,
                'courses' => $results,
                'interestingCourses'=>$interestingCourses
            ))
            ->getContent();

        $response = array(
            'modal' => $html,
        );

        return new Response( json_encode($response) );
    }
}