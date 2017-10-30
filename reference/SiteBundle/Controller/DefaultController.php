<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Spotlight;
use ClassCentral\SiteBundle\Entity\User;
use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Form\SignupType;
use ClassCentral\SiteBundle\Services\Image;
use ClassCentral\SiteBundle\Services\Kuber;
use ClassCentral\SiteBundle\Utility\Breadcrumb;
use ClassCentral\SiteBundle\Utility\CourseUtility;
use ClassCentral\SiteBundle\Utility\PageHeader\PageHeaderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Offering;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    private $autoLoginUrls = array(
        'https://www.class-central.com/report/best-programming-courses-data-science/',
        'https://www.class-central.com/report/mooc-based-masters-degree/',
        'https://www.class-central.com/report/9-popular-online-courses-gone-forever/'
    );
               
    public function indexAction(Request $request) {

        // Autologin if a token exists
        $this->get('user_service')->autoLogin($request);

        // Check whether there is a redirect. This is done to redirect users to login only areas of the site.
        $redirect = $request->get('redirect');
        if( !empty($redirect) && in_array($redirect, array('user_follows','user_recommendations')) )
        {
            return $this->redirect($this->generateUrl( $redirect ));
        }

        if( !empty($redirect))
        {
            $url = urldecode($redirect);
            if( in_array($url,$this->autoLoginUrls))
            {
                return $this->redirect( $url );
            }
        }

        $cache = $this->get('Cache');
        $esCourses = $this->get('es_courses');
        $em = $this->getDoctrine()->getManager();

        $spotlights = $cache->get('spotlight_cache',function(){
           $s = $this
                ->getDoctrine()->getManager()
                ->getRepository('ClassCentralSiteBundle:Spotlight')->findAll();

            $spotlights = array();
            foreach($s as $item)
            {
                if($item->getCourse())
                {
                    $item->setCourseId( $item->getCourse()->getId() ); // Cache the course id
                    $item->getProvider(); // Calling this here so that the provider name gets cached
                }
                $spotlights[$item->getPosition()] = $item;
            }

            return $spotlights;
        }, array());


        // Get Top 10 courses based on recent reviews.
        $data = $cache->get('trending_courses', function(){
            $cl = $this->get('course_listing');
            return $cl->trending();
        });

        $subjects = $cache->get('stream_list_count',
                        array( new StreamController(), 'getSubjectsList'),
                        array( $this->container )
        );

        // Get a list of courses taken by the signed in user as well as course recommendations
        $uc = array();
        $ucCount = 0;
        $recommendedCourses =  array(
            'allSubjects' => '',
            'courses' => '',
            'allLanguages' => '',
            'sortField' => '',
            'sortClass' => '',
            'pageNo' => '',
        );
        if( $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') )
        {
            $user = $this->get('security.context')->getToken()->getUser();

            $qb = $em->createQueryBuilder();
            $qb
                ->add('select', 'c.id as cid')
                ->add('from','ClassCentralSiteBundle:UserCourse uc')
                ->join('uc.course', 'c')
                ->andWhere('uc.user = :userId')
                ->setParameter('userId',$user->getId() )
                ->add('orderBy', 'uc.id DESC') // newest one top
                ;
            $results = $qb->getQuery()->getArrayResult();
            $courseIds = array();
            foreach($results as $result)
            {
                $courseIds[] = $result['cid'];
            }

            $ucCount = count( $courseIds );
            if( !empty($courseIds) )
            {
                $courseIds = array_splice( $courseIds, 0 , 10);
            }
            $response = $esCourses->findByIds( $courseIds );
            $uc = $response['results'];

            // Get recommendations details


            if( $user->areRecommendationsAvailable() )
            {
                // Get the courses
                $suggestions = $this->get('suggestions');
                $recommendedCourses = $suggestions->getRecommendations($user,$request->query->all());
                $recommendedCourses['courses']['hits']['hits'] = array_splice( $recommendedCourses['courses']['hits']['hits'],0,10);
            }

        }

        $meetYourNextCourse =false;
        if( !empty ($request->query->get('meet-your-next-course') ) )
        {
            $meetYourNextCourse =true;
            $this->get('user_session')->clearNextCourseFollows();

        }

        // MOOC Report posts
        $moocReport = $this->get('mooc_report');
        $newestPosts = array();
        $opEds = array();
        try{
            $newestPosts = $moocReport->getPosts();
            $opEds = $moocReport->getOpEds();
        }
        catch(\Exception $e)
        {

        }

        return $this->render('ClassCentralSiteBundle:Default:index.html.twig', array(
                'page' => 'home',
                'listTypes' => UserCourse::$lists,
                'trendingCourses'   => $data['courses'],
                'spotlights' => $spotlights,
                'spotlightMap' => Spotlight::$spotlightMap,
                'subjects' => $subjects,
                'uc' => $uc,
                'ucCount' => $ucCount,
                'recommendedCourses' => $recommendedCourses,
                'meetYourNextCourse' => $meetYourNextCourse,
                // 'spotlightPaidCourse' => $this->get('course')->getRandomPaidCourseExcludeByProvider('Treehouse'),
                //'spotlightCourseSecondRow' =>$this->get('course')->getRandomPaidCourseByProvider('Treehouse'),
                'newestPosts' => $newestPosts,
                'opEds' => $opEds
               ));
    }


    public function coursesAction(Request $request, $type = 'upcoming')
    {
        // Autologin if a token exists
        $this->get('user_service')->autoLogin($request);

        if(!in_array($type, array_keys(Offering::$types))){
            // TODO: render an error page
            return false;
        }

        $cl = $this->get('course_listing');
        $data = $cl->byTime($type,$request);

        return $this->render('ClassCentralSiteBundle:Default:courses.html.twig', 
                array(
                    'offeringType' => $type,
                    'page'=>'courses',
                    'results' => $data['courses'],
                    'listTypes' => UserCourse::$lists,
                    'allSubjects' => $data['allSubjects'],
                    'allLanguages' => $data['allLanguages'],
                    'numCoursesWithCertificates' => $data['numCoursesWithCertificates'],
                     'offeringTypes' => Offering::$types,
                    'sortField' => $data['sortField'],
                    'sortClass' => $data['sortClass'],
                    'pageNo' => $data['pageNo'],
                    'showHeader' => true
                ));
    }

    public function faqAction(Request $request) {
        $this->get('user_service')->autoLogin($request);
        $breadcrumbs = array(
            Breadcrumb::getBreadCrumb('FAQ', $this->container->get('router')->generate('ClassCentralSiteBundle_faq')),
        );
        return $this->render('ClassCentralSiteBundle:Default:faq.html.twig', array(
            'page' => 'faq',
            'breadcrumbs' => $breadcrumbs
        ));
    }

    public function privacyPolicyAction() {
        return $this->render('ClassCentralSiteBundle:Default:privacy.html.twig', array(
            'page' => 'privacy',
        ));
    }
    
    /**
     * 
     * Cache cant be cleared from the command line. So creating an action
     */
    public function clearCacheAction(){
        $this->get('cache')->clear();
        // Just adding a dummy page
        return $this->render('ClassCentralSiteBundle:Default:faq.html.twig', array('page' => 'faq'));
    }

    public function githubButtonAction()
    {
        if ($this->container->has('profiler'))
        {
            $this->container->get('profiler')->disable();
        }
        return $this->render('ClassCentralSiteBundle:Default:githubbtn.html.twig');
    }

    /**
     * If the token is valid it logs the user in and then redirects the user to
     * the destination url. This is done for paths that are behind Sfymony's
     * login firewall
     * @param Request $request
     */
    public function autoLoginSecureAction(Request $request)
    {
        // Autologin if a token exists
        $this->get('user_service')->autoLogin($request);

        $redirectUrl = $request->query->get('redirect_url');
        if( $redirectUrl )
        {
            return $this->redirect( $redirectUrl );
        }

    }

    /**
     * Showcase the deals i
     * @param Request $request
     */
    public function dealsAction(Request $request)
    {
        $dealsMeta = array(
            array('colour' => 'greenScheme' , 'numText' => 'One' ),
            array('colour' => 'yellowScheme' , 'numText' => 'Two' ),
            array('colour' => 'aquaScheme' , 'numText' => 'Three' )
        );
        
        $deals = array();
        while(count($deals) < 3 )
        {
            $deal = $this->get('course')->getRandomPaidCourse();
            if( empty($deals[$deal['id']]) && $deal['discounted_price'] > 0 )
            {
                $deal = array_merge($deal, array_shift($dealsMeta));
                $deals[$deal['id']] = $deal;
            }
        }

        return $this->render('ClassCentralSiteBundle:Default:deals.html.twig',array('deals' => $deals));
    }
    
}
