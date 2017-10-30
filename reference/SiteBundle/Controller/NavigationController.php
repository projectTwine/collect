<?php

namespace ClassCentral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Offering;

/**
 * Renders the navigation bar
 */
class NavigationController extends Controller{
    
     private $offeringCountCacheKey = 'navigation_offerings_count';
     private $initiativeCountCacheKey ='navigation_initiatives_count';
     private $streamCountCacheKey = 'navigation_stream_count';
     private $navEventName = 'navbar-clicks';
         
    
    public function indexAction($page)
    {
        $data = $this->getNavigationCounts( $this->container );
        // Start the session for every user
        $session = $this->getRequest()->getSession();
        if(!$session->isStarted())
        {
            // Start the session if its not already started
            $session->start();
        }

        return $this->render('ClassCentralSiteBundle:Helpers:navbar.html.twig', 
                            array( 'offeringCount' => $data['offeringCount'],'initiativeCount'=>$data['initiativeCount'],
                                   'page' => $page, 'offeringTypes'=> Offering::$types, 
                                    'initiativeTypes' => Initiative::$types,
                                    'navEventName' => $this->navEventName
                                ));  
    }

    public function getNavigationCounts( $container )
    {
        $cache = $container->get('cache');
        $em = $container->get('doctrine')->getManager();
        $data = $cache->get('navigation_counts', function($container){
            $esCourses = $container->get('es_courses');
            $counts = $esCourses->getCounts();
            $em = $container->get('doctrine')->getManager();

            $offeringCount = array();
            foreach (array_keys(Offering::$types) as $type)
            {
                if(isset($counts['sessions'][strtolower($type)]))
                {
                    $offeringCount[$type] = $counts['sessions'][strtolower($type)];
                }
                else
                {
                    $offeringCount[$type] = 0;
                }
            }

            $initiativeCount = array();
            foreach( Initiative::$types as $code => $name )
            {
                if($code == 'others')
                {
                    $initiativeCount[$name]['name'] = 'Others';
                }
                else
                {
                    $provider = $em->getRepository('ClassCentralSiteBundle:Initiative')->findOneBy( array('code' => $code) );
                    $initiativeCount[$name]['name'] = $provider->getName();
                }

                $initiativeCount[$name]['count'] = $counts['providersNav'][$code];

            }

            return compact('offeringCount','initiativeCount');

        }, array($container));

        return $data;
    }

}