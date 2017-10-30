<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 6/21/14
 * Time: 8:45 PM
 */

namespace ClassCentral\SiteBundle\Services;
use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Review as ReviewEntity;
use ClassCentral\SiteBundle\Entity\User as UserEntity;
use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Utility\Breadcrumb;
use ClassCentral\SiteBundle\Utility\PageHeader\PageHeaderFactory;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A service that builds data for course listing pages
 * like provider pages etc
 * Class CourseListing
 * @package ClassCentral\SiteBundle\Services
 */
class CourseListing {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieves all the data required for a particular provider
     * @param $slug
     * @param Request $request
     */
    public function byProvider($slug, Request $request)
    {
        $cache = $this->container->get('cache');

        $data = $cache->get(
            'provider_' . $slug . $request->server->get('QUERY_STRING'), function ($slug, $request) {

            $finder = $this->container->get('course_finder');

            $em = $this->container->get('doctrine')->getManager();

            if ($slug == 'others') {
                $provider = new Initiative();
                $provider->setName('Others');
                $provider->setCode('others');
            } elseif ($slug == 'independent') {
                $provider = new Initiative();
                $provider->setName('Independent');
                $provider->setCode('independent');
            } else {
                $provider = $em->getRepository('ClassCentralSiteBundle:Initiative')->findOneBy(array('code' => $slug));
                if (!$provider) {
                    throw new \Exception("Provider $slug not found");
                }
            }

            extract($this->getInfoFromParams($request->query->all()));
            $courses = $finder->byProvider($slug, $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            $pageInfo = PageHeaderFactory::get($provider);

            $breadcrumbs = array(
                Breadcrumb::getBreadCrumb('Providers', $this->container->get('router')->generate('providers')),
            );
            $breadcrumbs[] = Breadcrumb::getBreadCrumb($provider->getName());


            // get credentials
            $credentialService = $this->container->get('credential');
            $credParams = $credentialService->getCredentialsFilterParams($request->query->all());
            $credParams['provider'] = array(strtolower( $provider->getCode() ));
            $credData = $credentialService->getCredentialsInfo( $credParams );
            $credentials = $credData['credentials'];
            $numCredentials = $credData['numCredentials'];

            return compact(
                'provider', 'allSubjects', 'allLanguages', 'allSessions', 'courses',
                'sortField', 'sortClass', 'pageNo', 'pageInfo','breadcrumbs','numCoursesWithCertificates',
                'credentials','numCredentials'
            );
        }, array($slug, $request));

        return $data;
    }

    /**
     * Retrieves all the data required for a particular provider
     * @param $slug
     * @param Request $request
     */
    public function bySubject($slug, Request $request)
    {
        $cache = $this->container->get('cache');
        $data = $cache->get(
            'subject_' . $slug . $request->server->get('QUERY_STRING'), function ($slug, $request) {

            $finder = $this->container->get('course_finder');

            $em = $this->container->get('doctrine')->getManager();

            $subject = $em->getRepository('ClassCentralSiteBundle:Stream')->findOneBySlug($slug);

            if(!$subject)
            {
                throw new \Exception("Subject $slug not found");
                return;
            }

            extract($this->getInfoFromParams($request->query->all()));
            $courses = $finder->bySubject($slug, $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            $pageInfo = PageHeaderFactory::get($subject);
            $pageInfo->setPageUrl(
               $this->container->getParameter('baseurl'). $this->container->get('router')->generate('ClassCentralSiteBundle_stream', array('slug' => $slug))
            );

            $breadcrumbs = array(
                Breadcrumb::getBreadCrumb('Subjects', $this->container->get('router')->generate('subjects')),
            );

            // Add parent stream to the breadcrumb if it exists
            if($subject->getParentStream())
            {
                $breadcrumbs[] = Breadcrumb::getBreadCrumb(
                    $subject->getParentStream()->getName(),
                    $this->container->get('router')->generate('ClassCentralSiteBundle_stream', array( 'slug' => $subject->getParentStream()->getSlug()))
                );
            }

            $breadcrumbs[] = Breadcrumb::getBreadCrumb($subject->getName());
            $subject->setParentStream( null ); // To avoid cache errors

            // get credentials
            $credentialService = $this->container->get('credential');
            $credParams = $credentialService->getCredentialsFilterParams($request->query->all());
            $credParams['streams'] = array(strtolower( $subject->getSlug() ));
            $credData = $credentialService->getCredentialsInfo( $credParams );
            $credentials = $credData['credentials'];
            $numCredentials = $credData['numCredentials'];

            return compact(
                'subject', 'allSubjects', 'allLanguages', 'allSessions', 'courses',
                'sortField', 'sortClass', 'pageNo', 'pageInfo','breadcrumbs','numCoursesWithCertificates',
                'credentials','numCredentials', 'tags'
            );
        }, array($slug, $request));

        return $data;
    }

    public function byTime($status, Request $request)
    {
        $cache = $this->container->get('cache');
        $data = $cache->get(
            'course_status_' . $status . $request->server->get('QUERY_STRING'), function ($status, $request) {

            $finder = $this->container->get('course_finder');

            $params = $request->query->all();
            if($status == 'selfpaced' && empty($params['sort']) )
            {
                // make the default sort by rating
                $params['sort'] = 'rating-up';
            }

            extract($this->getInfoFromParams( $params ));

            $courses = $finder->byTime($status, $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            return compact(
               'allSubjects', 'allLanguages', 'courses',
                'sortField', 'sortClass', 'pageNo','numCoursesWithCertificates'
            );
        }, array($status, $request));

        return $data;
    }

    public function getAll(Request $request)
    {
        $cache = $this->container->get('cache');
        $data = $cache->get(
            'course_get_all_' . $request->server->get('QUERY_STRING'), function ($request) {

            $finder = $this->container->get('course_finder');

            $params = $request->query->all();
            if( empty($params['sort']) )
            {
                // make the default sort by rating
                $params['sort'] = 'rating-up';
            }

            extract($this->getInfoFromParams( $params ));

            $courses = $finder->getAll( $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            return compact(
                'allSubjects', 'allLanguages', 'courses',
                'sortField', 'sortClass', 'pageNo','numCoursesWithCertificates'
            );
        }, array($request));

        return $data;
    }

    public function byFollows($follows, $params, $must, $mustNot = array())
    {
        $finder = $this->container->get('course_finder');
        $params['session'] = "upcoming,selfpaced";
        extract($this->getInfoFromParams( $params ));

        $courses = $finder->byFollows($follows, $filters, array(), $pageNo,$must, $mustNot);
        extract($this->getFacets($courses));

        return compact(
            'allSubjects', 'allLanguages', 'courses',
            'sortField', 'sortClass', 'pageNo','numCoursesWithCertificates'
        );


        return $data;
    }

    public function byInstitution($slug, Request $request)
    {
        $cache = $this->container->get('cache');
        $data = $cache->get(
            'institution_' . $slug . $request->server->get('QUERY_STRING'), function ($slug, $request) {

            $finder = $this->container->get('course_finder');
            $em = $this->container->get('doctrine')->getManager();

            $institution = $em->getRepository('ClassCentralSiteBundle:Institution')->findOneBySlug($slug);
            if(!$institution) {
                throw new \Exception("Institution/University $slug not found");
            }

            $pageInfo =  PageHeaderFactory::get($institution);
            $pageInfo->setPageUrl(
                $this->container->getParameter('baseurl'). $this->container->get('router')->generate('ClassCentralSiteBundle_institution', array('slug' => $slug))
            );

            extract($this->getInfoFromParams($request->query->all()));
            $courses = $finder->byInstitution($slug, $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            if( $institution->getIsUniversity() )
            {
                $breadcrumbs = array(
                    Breadcrumb::getBreadCrumb('Universities', $this->container->get('router')->generate('universities')),
                );
            }
            else
            {
                $breadcrumbs = array(
                    Breadcrumb::getBreadCrumb('Institutions', $this->container->get('router')->generate('institutions')),
                );
            }

            $breadcrumbs[] = Breadcrumb::getBreadCrumb($institution->getName());


            return compact(
                'allSubjects', 'allLanguages', 'allSessions', 'courses',
                'sortField', 'sortClass', 'pageNo', 'pageInfo', 'institution', 'breadcrumbs','numCoursesWithCertificates'
            );
        }, array($slug, $request));

        return $data;
    }

    public function byLanguage($slug, Request $request)
    {
        $cache = $this->container->get('cache');
        $data = $cache->get(
            'language_' . $slug . $request->server->get('QUERY_STRING'), function ($slug, $request) {

            $finder = $this->container->get('course_finder');
            $em = $this->container->get('doctrine')->getManager();

            $language = $em->getRepository('ClassCentralSiteBundle:Language')->findOneBySlug($slug);
            if(!$language) {
                throw new \Exception("Language $slug not found");
            }
            $pageInfo =  PageHeaderFactory::get($language);
            $pageInfo->setPageUrl(
                $this->container->getParameter('baseurl'). $this->container->get('router')->generate('lang', array('slug' => $slug))
            );

            $breadcrumbs = array(
                Breadcrumb::getBreadCrumb('Languages',$this->container->get('router')->generate('languages')),
                Breadcrumb::getBreadCrumb($language->getName(), $this->container->get('router')->generate('lang',array('slug' => $language->getSlug())))
            );

            extract($this->getInfoFromParams($request->query->all()));
            $courses = $finder->byLanguage($slug, $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            return compact(
                'allSubjects', 'allSessions', 'courses','numCoursesWithCertificates',
                'sortField', 'sortClass', 'pageNo', 'pageInfo', 'breadcrumbs','language'
            );
        }, array($slug, $request));

        return $data;
    }

    public function byTag($tag, Request $request)
    {
        $cache = $this->container->get('cache');
        $tagKey = str_replace(' ', '_',$tag);
        $data = $cache->get(
            'tag_' . $tagKey . $request->server->get('QUERY_STRING'), function ($tag, $request) {

            $finder = $this->container->get('course_finder');

            $em = $this->container->get('doctrine')->getManager();

            $tagEntity = $em->getRepository('ClassCentralSiteBundle:Tag')->findOneByName($tag);

            extract($this->getInfoFromParams($request->query->all()));
            $courses = $finder->byTag($tag, $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            return compact(
                'allSubjects', 'allLanguages', 'allSessions', 'courses',
                'sortField', 'sortClass', 'pageNo', 'tagEntity','numCoursesWithCertificates'
            );
        }, array($tag, $request));

        return $data;
    }

    public function byCareer($slug, Request $request)
    {
        $cache = $this->container->get('cache');
        $data = $cache->get(
            'career' . $slug . $request->server->get('QUERY_STRING'), function ($slug, $request) {

            $finder = $this->container->get('course_finder');
            $em = $this->container->get('doctrine')->getManager();

            $career = $em->getRepository('ClassCentralSiteBundle:Career')->findOneBySlug($slug);
            if(!$career) {
                throw new \Exception("Institution/University $slug not found");
            }

            $pageInfo =  PageHeaderFactory::get($career);
            $pageInfo->setPageUrl(
                $this->container->getParameter('baseurl'). $this->container->get('router')->generate('career_page', array('slug' => $slug))
            );

            extract($this->getInfoFromParams($request->query->all()));
            $courses = $finder->byCareer($slug, $filters, $sort, $pageNo);
            extract($this->getFacets($courses));

            $breadcrumbs = array(
                Breadcrumb::getBreadCrumb('Careers', $this->container->get('router')->generate('careers')),
            );


            $breadcrumbs[] = Breadcrumb::getBreadCrumb($career->getName());


            return compact(
                'allSubjects', 'allLanguages', 'allSessions', 'courses',
                'sortField', 'sortClass', 'pageNo', 'pageInfo', 'career', 'breadcrumbs',
                'numCoursesWithCertificates'
            );
        }, array($slug, $request));

        return $data;
    }

    public function search($keyword, Request $request)
    {
        $finder = $this->container->get('course_finder');

        extract($this->getInfoFromParams($request->query->all()));
        $sort = Filter::getQuerySort($request->query->all(),array(
            "_score" => array(
                "order" => "desc"
            )));
        $courses = $finder->search($keyword, $filters, $sort, $pageNo);
        extract($this->getFacets($courses));

        return compact(
            'allSubjects', 'allLanguages', 'allSessions', 'courses',
            'sortField', 'sortClass', 'pageNo','numCoursesWithCertificates'
        );
    }

    public function userLibrary(UserEntity $user, Request $request)
    {
        $finder = $this->container->get('course_finder');

        $userCourses = $user->getUserCourses();
        $courseIds = array();
        $courseIdsByList = array();
        $coursesByLists = array();
        $listCounts = array();

        $lists = Filter::getUserList( $request->query->all() );
        foreach($lists as $list)
        {
            $listCounts[$list] = 0;
            $courseIdsByList[ $list ] = array();
        }
        foreach($userCourses as $userCourse)
        {
            $list = $userCourse->getList();
            if( in_array( $list['slug'],$lists) )
            {
                $courseIds[] = $userCourse->getCourse()->getId();
                $listCounts[$list['slug']]++;
                $courseIdsByList[ $list['slug'] ][] = $userCourse->getCourse()->getId();
            }
        }

        extract($this->getInfoFromParams($request->query->all()));
        foreach($lists as $list)
        {
            if( !empty(  $courseIdsByList[ $list ] ) )
            {
                $coursesByLists[$list] = $finder->byCourseIds( $courseIdsByList[ $list ], $filters, $sort, -1 );
            }
            else
            {
                $coursesByLists[$list] = array();
            }
        }

        $courses = $finder->byCourseIds($courseIds, $filters, $sort, $pageNo);
        extract($this->getFacets($courses));

        // Get the search terms
        $userSession = $this->container->get('user_session');
        $searchTerms = $userSession->getMTSearchTerms();
        $showInstructions = false;
        if( empty($searchTerms) && empty($lists) )
        {
            $showInstructions = true;
        }

        // Get Reviewed Courses
        $reviewedCourseIds = array();
        foreach( $user->getReviews() as $review )
        {
            $reviewedCourseIds[] = $review->getCourse()->getId();
        }

        $reviewedCourses = array();
        if( !empty($reviewedCourseIds) )
        {
            $reviewedCourses = $finder->byCourseIds($reviewedCourseIds);
        }

        return compact(
            'allSubjects', 'allLanguages', 'allSessions', 'courses',
            'sortField', 'sortClass', 'pageNo','lists', 'listCounts','coursesByLists','showInstructions',
            'searchTerms', 'reviewedCourses','numCoursesWithCertificates'
        );
    }

    public function byCourseIds( $courseIds = array() )
    {
        $finder = $this->container->get('course_finder');
        $courses = $finder->byCourseIds($courseIds);
        extract($this->getFacets($courses));

        return compact(
            'allSubjects', 'allLanguages', 'allSessions', 'courses',
            'sortField', 'sortClass', 'pageNo','lists', 'listCounts','coursesByLists','showInstructions',
            'searchTerms', 'reviewedCourses','numCoursesWithCertificates'
        );
    }

    public function collection($courseIds = array(),Request $request,$additionalParams = array())
    {
        $finder = $this->container->get('course_finder');
        $params =  $request->query->all();
        if(empty($params['sort']))
        {
            $params['sort'] = 'rating-up';
        }
        if(!empty($additionalParams['session']))
        {
            $params['session'] = $additionalParams['session'];
        }

        extract( $this->getInfoFromParams($params) );
        $courses = $finder->byCourseIds($courseIds,$filters, $sort, $pageNo);
        extract($this->getFacets($courses));

        return compact(
            'allSubjects', 'allLanguages', 'allSessions', 'courses',
            'sortField', 'sortClass', 'pageNo','lists', 'listCounts','coursesByLists','showInstructions',
            'searchTerms', 'reviewedCourses','numCoursesWithCertificates'
        );
    }

    public function trending()
    {
        $date = new \DateTime();
        $date->sub( new \DateInterval('P14D') );
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('course_id','course_id');
        $reviewStatusNotShownLowerBound = ReviewEntity::REVIEW_NOT_SHOWN_STATUS_LOWER_BOUND;
        $q = $this->container->get('doctrine')->getManager()->createNativeQuery("
            SELECT course_id FROM reviews WHERE created > '{$date->format('Y-m-d')}' AND status < {$reviewStatusNotShownLowerBound}  GROUP BY course_id ORDER BY count(*) DESC LIMIT 10;
        ", $rsm);

        $results = $q->getResult();
        $trendingCourseIds = array();
        foreach($results as $result)
        {
            $trendingCourseIds[] = $result['course_id'];
        }

        $data = $this->byCourseIds( $trendingCourseIds );

        // Sort the courses by Trending Ids
        $newHits = array();
        foreach($trendingCourseIds as $cid)
        {
            foreach($data['courses']['hits']['hits'] as $hit)
            {
                if($hit['_id'] == $cid)
                {
                    $newHits[] = $hit;
                    break;
                }
            }
        }
        $data['courses']['hits']['hits'] = $newHits;

        return $data;
    }

    public function getInfoFromParams($params = array())
    {
        $filters = Filter::getQueryFilters($params);
        $sort = Filter::getQuerySort($params);
        $pageNo = Filter::getPage($params);
        $sortField = '';
        $sortClass = '';
        if (isset($params['sort'])) {
            $sortDetails = Filter::getSortFieldAndDirection($params['sort']);
            $sortField = $sortDetails['field'];
            $sortClass = Filter::getSortClass($sortDetails['direction']);
        }

        return compact('filters', 'sort', 'pageNo', 'sortField', 'sortClass');
    }

    public function getFacets( $courses )
    {
        $finder =  $this->container->get('course_finder');
        $filter = $this->container->get('filter');
        $facets = $finder->getFacetCounts( $courses );
        $allSubjects = $filter->getCourseSubjects( $facets['subjectIds'] );
        $allLanguages = $filter->getCourseLanguages( $facets['languageIds'] );
        $allSessions  = $filter->getCourseSessions( $facets['sessions'] );
        $tags = $facets['tags'];
        $numCoursesWithCertificates = $facets['certificates'];

        return compact('allSubjects','allLanguages','allSessions','numCoursesWithCertificates','tags');
    }
} 