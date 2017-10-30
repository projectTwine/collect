<?php

namespace ClassCentral\SiteBundle\Services;

use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Entity\UserCourse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Filter {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getCourseSubjects( $subjectIds = array())
    {
        $cache = $this->container->get('cache');
        // Get the entire subject tree
        $allSubjects = $cache->get('allSubjects', array($this,'getSubjectsTree'));

        // Filter out the subjects that are not mentioned in these offerings
        $courseSubjectIds = array_keys( $subjectIds );
        foreach($allSubjects as $parent)
        {
            $hasChild = false;
            foreach($parent['children'] as $child)
            {
                if(!in_array($child['id'],$courseSubjectIds))
                {
                    unset($allSubjects[$parent['slug']]['children'][$child['slug']]);
                }
                else
                {
                    $hasChild = true;
                }
            }

            if(!$hasChild && !in_array($parent['id'],$courseSubjectIds))
            {
                unset($allSubjects[$parent['slug']]);
            }
            else
            {
                $allSubjects[$parent['slug']]['count'] = $subjectIds[$parent['id']];
            }
        }

        return $allSubjects;
    }

    /**
     * Builds a subject tree
     * @return array
     */
    public function getSubjectsTree()
    {
        $em = $this->container->get('Doctrine')->getManager();
        $allSubjects = $em->getRepository('ClassCentralSiteBundle:Stream')->findAll();
        $subjects = array();
        foreach($allSubjects as $subject)
        {
            if($subject->getParentStream())
            {
                //$childSubjects[$subject->getParentStream()->getId()][] = $subject;
            }
            else
            {
                $subjects[$subject->getSlug()] = array(
                    'name' => $subject->getName(),
                    'id' => $subject->getId(),
                    'slug'=> $subject->getSlug(),
                );

                $children = array();
                /*
                foreach($subject->getChildren() as $childSub)
                {
                    $children[$childSub->getSlug()] = array(
                        'name' => $childSub->getName(),
                        'id' => $childSub->getId(),
                        'slug'=> $childSub->getSlug()
                    );

                }
                */
                $subjects[$subject->getSlug()]['children'] = $children;
            }
        }

        return $subjects;
    }


    public function getCourseLanguages($languageIds = array())
    {
        $cache = $this->container->get('cache');

        // Get language info
        $allLanguages = $cache->get('allLanguages', array($this,'getLanguages'));
        $courseLangIds = array_keys( $languageIds );
        foreach($allLanguages as $lang)
        {
            $name = $lang['name'];
            if( !in_array($lang['id'],$courseLangIds) )
            {
                unset($allLanguages[$name]);
            }
            else
            {
                $allLanguages[$name]['count'] = $languageIds[ $lang['id'] ];
            }
        }

        return $allLanguages;
    }
    public function getLanguages()
    {
        $em = $this->container->get('Doctrine')->getManager();

        $languages = array();
        foreach($em->getRepository('ClassCentralSiteBundle:Language')->findAll() as $lang)
        {
            $languages[$lang->getName()] = array(
                'name' => $lang->getName(),
                'id' => $lang->getId()
            );
        }

        return $languages;
    }

    public function getCourseSessions ($sessions = array())
    {
        $s = array();
        $allSessions = Offering::$types;
        $sessionKeys = array_keys( $sessions );
        foreach($allSessions as $key => $value)
        {
            if ( in_array(strtolower($key),$sessionKeys) )
            {
                $value['count'] = $sessions[ strtolower($key) ];
                $s[$key] = $value;
            }
        }

        return $s;
    }

    /**
     * Generate filters from query string for elastic search
     * @param $params
     */
    public static function getQueryFilters( $params = array() )
    {
        $and = array();
        if( isset($params['session']) )
        {
            $and[] = self::getTermsQuery( 'nextSession.states',$params['session']);
        }

        if( isset($params['lang']) )
        {
            $and[] = self::getTermsQuery( 'language.slug',$params['lang']);
        }

        if ( isset( $params['subject'] ) )
        {
            $and[] = self::getTermsQuery('subjects.slug', $params['subject']);
        }

        if( isset($params['certificate']))
        {
            $and[] = self::getTermsQuery('certificate',true);
        }

        /**
         * Whenever the results are sorted by dates,
         * remove the courses which have a start date that is unknown
         */
        if (isset($params['sort']))
        {
            // Split the field and direction
            $lastHypen = strrpos($params['sort'], '-');
            $field = substr($params['sort'], 0, $lastHypen);

            if( $field == 'date') {
                $and[] = array(
                    'range' =>array(
                        'nextSession.status' => array(
                            "gt" => 0
                        )
                ));
            }
        }


        if( !empty($and) )
        {
            return array(
                'and' => $and
            );
        }

        return array();
    }

    private static function getTermsQuery( $key, $values )
    {
        return array(
          'terms' => array(
              $key => array_map( "strtolower",explode(',',$values) ),
              'execution' => "or"
          )
        );
    }

    /**
     * @param array $params
     * @param array $default when params are empty this one is used
     * @return array
     */
    public static function getQuerySort($params = array(),$default = array())
    {
        $sortOrder = array();
        $addedStartDate = false;

        if (isset($params['sort'])) {
            // Split the field and direction
            $lastHypen = strrpos($params['sort'], '-');
            $field = substr($params['sort'], 0, $lastHypen);
            $direction = substr($params['sort'], $lastHypen + 1);

            $sortType = self::getSortOrder( $sortOrder );

            $sort = self::getSortOrder($direction);
            if ($field == 'rating') {
                $sortOrder [] = array(
                    'ratingSort' => array(
                        'order' => $sort
                    )
                );
            }

            if ($field == 'name') {
                $sortOrder [] = array(
                    'name.raw' => array(
                        'order' => $sort
                    )
                );
            }

            if( $field == 'date') {
                $addedStartDate = true;
                $sortOrder[] = array(
                    "nextSession.startDate" => array(
                        "order" => $sort
                    ));
            }
        }

        if( !empty($default))
        {
            $sortOrder[] = $default;
        }
        $sortOrder[] = array(
            "nextSession.state" => array(
                "order" => "desc"
            ));

        if( !$addedStartDate )
        {
            $sortOrder[] = array(
                "nextSession.startDate" => array(
                    "order" => "asc"
            ));
        }

        return $sortOrder;

    }

    /**
     *
     * @param $sort
     * @return array
     */
    public static function getSortFieldAndDirection( $sort )
    {
        $lastHyphen = strrpos($sort, '-');
        $field = substr($sort, 0, $lastHyphen);
        $direction = substr($sort, $lastHyphen + 1);

        return array(
            'field' => $field,
            'direction' => $direction
        );
    }

    public static function getSortClass( $direction )
    {
        return ( $direction == 'down' ) ? 'headerSortDown' : 'headerSortUp';
    }

    private static function getSortOrder($direction)
    {
        if( empty($direction) )
        {
            return '';
        }
        return ($direction == 'down') ? 'asc' : 'desc';
    }

    public  static function getPage($params)
    {
        if( empty($params['page']) )
        {
            return 1;
        }

        $page = intval( $params['page']);

        return $page;
    }

    public static function getUserList( $params )
    {
        if(empty($params) || empty($params['list']))
        {
            return UserCourse::getListTypes();
        }

        return explode( ',', $params['list']);
    }

} 