<?php

namespace ClassCentral\SiteBundle\Services;

use ClassCentral\SiteBundle\Entity\Item;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Api
{
  private $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function subjectsList()
  {
      $em = $this->container->get('doctrine')->getManager();
      $followService = $this->container->get('follow');
      $esCourses = $this->container->get('es_courses');
      $counts = $esCourses->getCounts();
      $router =  $this->container->get('router');

      $popularSubjects = [
          'cs','business','humanities','data-science','personal-development','art-and-design',
          'engineering','health','maths','science','education','programming-and-software-development'
      ];

      $subjects = [];
      foreach ($popularSubjects as $subjectSlug)
      {
        $subject = [];
        $sub = $em->getRepository('ClassCentralSiteBundle:Stream')->findOneBy(array('slug'=> $subjectSlug));
        $subject['name'] = $sub->getName();
        $subject['slug'] = $subjectSlug;
        $subject['numFollows'] = $followService->getNumFollowers(Item::ITEM_TYPE_SUBJECT, $sub->getId());
        $subject['numCourses'] = $counts['subjects'][$sub->getId()];
        $subject['url'] = $router->generate('ClassCentralSiteBundle_stream',array('slug' => $subjectSlug));

        $subjects[] = $subject;
      }

      return $subjects;

  }

  public function providersList()
  {
      $popularProviders = array('coursera','udacity','edx','futurelearn');
      $em = $this->container->get('doctrine')->getManager();
      $followService = $this->container->get('follow');
      $esCourses = $this->container->get('es_courses');
      $counts = $esCourses->getCounts();
      $router =  $this->container->get('router');

      $providers = [];
      foreach ($popularProviders as $providerSlug)
      {
          $provider = [];
          $prov = $em->getRepository('ClassCentralSiteBundle:Initiative')->findOneBy(array('code' => $providerSlug));
          $provider['name'] = $prov->getName();
          $provider['slug'] = $providerSlug;
          $provider['numFollows'] = $followService->getNumFollowers(Item::ITEM_TYPE_PROVIDER,$prov->getId());
          $provider['numCourses'] = $counts['providers'][$providerSlug];
          $provider['url'] = $router->generate('ClassCentralSiteBundle_initiative',
              array('type' => $providerSlug ));

          switch ($provider['slug']):
            case 'coursera':
              $provider['description'] = "Coursera, a company founded by Stanford professors offers online courses from over 140 universities.";
              break;
            case 'udacity':
              $provider['description'] = "Udacity, born out of a Stanford experiment, partners with tech companies to offer career-focused courses.";
              break;
            case 'edx':
              $provider['description'] = "edX is a nonprofit MOOC provider founded by Harvard and MIT. It has around 100 university partners.";
              break;
            case 'futurelearn':
              $provider['description'] = "FutureLearn is a UK-based provider with 130 partners and has a focus on social learning.";
              break;
          endswitch;

          $providers[] = $provider;
      }

      return $providers;
  }

  public function universitiesList()
  {
      $popularUniversities = [
          'stanford','mit','harvard','gatech','tsu','iimb','delft','ubc', 'umich'
      ];
      $em = $this->container->get('doctrine')->getManager();
      $followService = $this->container->get('follow');
      $esCourses = $this->container->get('es_courses');
      $counts = $esCourses->getInstitutionCounts(true);
      $router =  $this->container->get('router');

      $universities = [];
      foreach ($popularUniversities as $universitySlug)
      {
          $university = [];
          $uni = $em->getRepository('ClassCentralSiteBundle:Institution')->findOneBy(array('slug' => $universitySlug));
          $university['name'] = $uni->getShortAlias();
          $university['slug'] = $universitySlug;
          $university['numFollows'] = $followService->getNumFollowers(Item::ITEM_TYPE_INSTITUTION,$uni->getId());
          $university['numCourses'] = $counts['institutions'][$universitySlug];
          $university['url'] = $router->generate('ClassCentralSiteBundle_university',[
              'slug' => $universitySlug
          ]);

          $universities[] = $university;

      }

      return $universities;
  }

  public function notableList()
  {
      $esCourses = $this->container->get('es_courses');
      $counts = $esCourses->getCounts();
      $router =  $this->container->get('router');
      $justAnnouncedCoursesCount = isset($counts['sessions']['recentlyadded'])? $counts['sessions']['recentlyadded']:0;
      $selfPacedCoursesCount = isset($counts['sessions']['selfpaced'])? $counts['sessions']['selfpaced']:0;
      $startsNowCoursesCount = isset($counts['sessions']['recent'])? $counts['sessions']['recent']:0;

      $trendingSection = [
          'trending' => [
              'url' => '/trending',
              'name' => 'Trending',
              'numCourses' => 10
          ],
          'justAnnounced' => [
              'url' => '/courses/recentlyAdded',
              'name' => 'Just Announced',
              'numCourses' => $justAnnouncedCoursesCount
          ],
          'selfpaced' => [
              'url' => '/courses/selfpaced',
              'name' => 'Self Paced',
              'numCourses' => $selfPacedCoursesCount
          ],
          'startsNow' => [
              'url' => '/courses/recent',
              'name' => 'Starts Now',
              'numCourses' => $startsNowCoursesCount
          ]
      ];

      // Top 50 courses
      $top50CourseIds = $this->container->get('course')->getCollection('top-free-online-courses');
      $data = $this->container->get('course_listing')->collection($top50CourseIds['courses'],new Request(),[]);
      $top50Courses = $data['courses']['hits']['hits'];

      $randomTop50 = [];
      while(count($randomTop50) < 3)
      {
          $courseRank = rand(0,49);
          if(isset($top50Courses[$courseRank]['_source']) && !isset($randomTop50[$top50Courses[$courseRank]['_source']['id']]))
          {
              $course = $top50Courses[$courseRank]['_source'];
              $institutionName = '';
              if(!empty($course['institutions']))
              {
                  $institutionName = $course['institutions'][0]['name'];
              }

              $top50Course = [
                  'name' => $course['name'],
                  'id' => $course['id'],
                  'slug' => $course['slug'],
                  'provider' => $course['provider']['name'],
                  'rating' => $course['rating'],
                  'numRatings' => $course['reviewsCount'],
                  'url' => $router->generate('ClassCentralSiteBundle_mooc',[
                      'slug' => $course['slug'],
                      'id' => $course['id']
                  ]),
                  'top50Rank' => $courseRank + 1,
                  'institution' => $institutionName
              ];
              $randomTop50[$course['id']] = $top50Course;
          }
      }

      usort($randomTop50,function($a,$b){
          return $a['top50Rank'] > $b['top50Rank'];
      });

      $notableSection = [
              'newAndTrending' => $trendingSection,
              'topFifty' => $randomTop50
      ];

    return $notableSection;
  }

  public function articles()
  {
      $articles = [
          [
              'title' => 'MOOCs Find Their Audience: Professional Learners and Universities',
              'url' => '/report/moocs-find-audience-professional-learners-universities/',
              'description' => 'The real audience for MOOCs is not the traditional university student but a “lifelong career learner.”'
          ],
          [
              'title' => 'Massive List of MOOC Providers Around The World',
              'url' => '/report/mooc-providers-list/',
              'description' => 'Where to Find MOOCs: The Definitive Guide to MOOC Providers'
          ],
          [
              'title' => 'Decoding edX’s Newest Credential: Professional Certificate Programs',
              'url' => '/report/edx-professional-certificate/',
              'description' => 'We take a look at what edX could be looking to achieve in launching "another" certificate program'
          ]
      ];
    return $articles;
  }

  public function getUserInfo()
  {
      $router =  $this->container->get('router');
      $userService = $this->container->get('user_service');

      $userId = null;
      $navMenu = null;
      $loggedIn = false;
      $name = '';
      $firstName = '';
      if($this->container->get('security.context')->getToken()->getUser() instanceof \ClassCentral\SiteBundle\Entity\User)
      {
          $user = $this->container->get('security.context')->getToken()->getUser();
          $userId = $user->getId();
          $loggedIn = true;
          $name = $user->getDisplayName();
          $firstName = $user->getFirstName();
          $navMenu = [
              'My Courses' => $router->generate('user_library'),
              'My Profile' => $userService->getProfileUrl($user->getId(),$user->getHandle()),
              'My Reviews' => $router->generate('user_reviews'),
              'Preferences' => $router->generate('user_preferences'),
              'Follows' => $router->generate('user_follows'),
              'Recommendations' => $router->generate('user_recommendations'),
              'Logout' => $router->generate('logout')
          ];
      }

      $loggedOutMenu = array(
        'Sign in' => '/login',
        'Register' => '/register',
      );

      $user = [
          'id' => $userId,
          'name' => $name,
          'firstName' => $firstName,
          'loggedIn' => $loggedIn,
          'navMenu' => $loggedIn ? $navMenu : $loggedOutMenu
      ];

      return $user;
  }


  public function getNavbarData()
  {

      $router = $this->container->get('router');
      $request = $this->container->get('request');

      return [
          'collections' =>[
              'notable' => $this->notableList(),
              'subject' => [
                  'url' => $router->generate('subjects'),
                  'cta' => 'See all Subjects',
                  'data' => $this->subjectsList()
              ],
              'university' => [
                  'url' =>$router->generate('universities'),
                  'cta' => 'See all Universties',
                  'data' => $this->universitiesList()
              ],
              'provider' => [
                  'url' => $router->generate('providers'),
                  'cta' => 'See all Providers',
                  'data' => $this->providersList()
              ],
              'articles' => $this->articles(),
          ],
          'user' => $this->getUserInfo(),
          'query' => $request->query->get('q')
      ];
  }
}
