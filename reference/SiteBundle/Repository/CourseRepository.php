<?php

namespace ClassCentral\SiteBundle\Repository;


use ClassCentral\CredentialBundle\Services\Credential;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\CourseStatus;
use ClassCentral\SiteBundle\Entity\Interview;
use ClassCentral\SiteBundle\Utility\CourseUtility;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class CourseRepository extends EntityRepository{

    /**
     * Takes a course entity and builds an array so that
     * it can be serialized and saved in a cache
     * @param Course $course
     */
    public function getCourseArray( Course $course, $addCourseInfo = array())
    {
        $courseDetails = array();
        $formatter = $course->getFormatter();

        $courseDetails['id'] = $course->getId();
        $courseDetails['name'] = $course->getName();
        $courseDetails['videoIntro'] = $course->getVideoIntro();
        $courseDetails['videoEmbedUrl'] = $this->getVideoEmbedUrl($course->getVideoIntro());
        $courseDetails['length'] = $course->getLength();
        $courseDetails['slug'] = $course->getSlug();
        $courseDetails['url'] = $course->getUrl();
        $courseDetails['nextOffering'] = null;
        $courseDetails['imageUrl'] = CourseUtility::getImageUrl($course);
        $courseDetails['status'] = $course->getStatus();
        $courseDetails['certificate'] = $course->getCertificate();
        $courseDetails['certificateDisplay'] = $formatter->getCertificate();
        $courseDetails['verifiedCertificate'] = $course->getVerifiedCertificate();
        $courseDetails['workloadMin'] = $course->getWorkloadMin();
        $courseDetails['workloadMax'] = $course->getWorkloadMax();
        $courseDetails['workloadType'] = $course->getWorkloadType();
        $courseDetails['workloadDisplay'] = $formatter->getWorkload();
        $courseDetails['durationMin'] = $course->getDurationMin();
        $courseDetails['durationMax'] = $course->getDurationMax();
        $courseDetails['durationDisplay'] = $formatter->getDuration();
        $courseDetails['price'] = $course->getPrice();
        $courseDetails['pricePeriod'] = $course->getPricePeriod();
        $courseDetails['priceDisplay'] = $formatter->getPrice();


        // Calculate the workload
        $workload = '';
        if( $course->getWorkloadMin() &&  $course->getWorkloadMax() )
        {
            if( $course->getWorkloadMin() == $course->getWorkloadMax() )
            {
                $workload = $course->getWorkloadMin() . ' hours/week';
            }
            else
            {
                $workload = $course->getWorkloadMin() . "-" . $course->getWorkloadMax() . ' hours/week';
            }
        }
        $courseDetails['workload'] = $workload;

        // Get the descriptions
        $desc = null;
        $shortDesc = $course->getDescription(); // this text only. No html. Goes in the head description
        $longDesc = $course->getLongDescription(); //html
        $syllabus = $course->getSyllabus(); // html

        if(empty($longDesc))
        {
            $desc = nl2br( $shortDesc );
        }
        else
        {
            $desc = $longDesc;
        }

        if(strlen($shortDesc) > 500)
        {
            $shortDesc = substr($shortDesc,0,497) . '...';
        }

        $courseDetails['shortDesc'] =$shortDesc;
        $courseDetails['syllabus'] = $syllabus;
        $courseDetails['longDesc'] = $longDesc;
        $courseDetails['desc'] = $desc;
        $courseDetails['oneLiner'] = $course->getOneliner();

        $nextOffering = $course->getNextOffering();
        if($nextOffering) {
            $courseDetails['nextOffering']['displayDate'] = $nextOffering->getDisplayDate();
            $courseDetails['nextOffering']['id'] = $nextOffering->getId();
            $courseDetails['nextOffering']['url'] = $nextOffering->getUrl();

            // Get the state of this session
            $courseDetails['state'] = null;
            $states = array_intersect( array('past','ongoing','selfpaced','upcoming'), CourseUtility::getStates( $nextOffering ));
            if(!empty($states))
            {
                $courseDetails['nextOffering']['state'] = array_pop($states);
            }
        }
        $courseDetails['tags'] = array();
        foreach( $course->getTags() as $tag)
        {
            $name = $tag->getName();
            if( !empty($name) ) // To account for bug which adds empty tags to courses
            {
                $courseDetails['tags'][] = $name;
            }
        }

        $courseDetails['listed'] = $this->getListedCount($course);
        // Stream
        $stream = $course->getStream();
        $courseDetails['stream']['name'] = $stream->getName();
        $courseDetails['stream']['slug'] = $stream->getSlug();
        $courseDetails['stream']['showInNav'] = $stream->getShowInNav();
        $courseDetails['stream']['id'] = $stream->getId();

        $secondarySubjects = [];
        foreach ($course->getSubjects() as $subject)
        {
            $secondarySubjects[] = [
                'name' => $subject->getName(),
                'slug' => $subject->getSlug(),
                'id' => $subject->getId()
            ];
        }

        $courseDetails['subjects'] = $secondarySubjects;

        // Initiative
        $initiative = $course->getInitiative();
        $courseDetails['initiative']['name'] = '';
        if ($initiative != null)
        {
            $courseDetails['initiative']['name'] = $initiative->getName();
            $courseDetails['initiative']['url'] = $initiative->getUrl();
            $courseDetails['initiative']['tooltip'] = $initiative->getTooltip();
            $courseDetails['initiative']['code'] = strtolower($initiative->getCode());
            $courseDetails['initiative']['id'] = $initiative->getId();
        }
        else
        {
            $courseDetails['initiative']['name'] = 'Independent';
            $courseDetails['initiative']['code'] = 'independent';
            $courseDetails['initiative']['tooltip'] = 'Independent';
            $courseDetails['initiative']['id'] = -1;
        }

        // Language
        $lang = array();
        if($course->getLanguage())
        {
            $l = $course->getLanguage();
            $lang['name'] = $l->getName();
            $lang['slug'] = $l->getSlug();
            $lang['code'] = $l->getCode();
        }
        $courseDetails['lang'] = $lang;


        // Institutions
        $courseDetails['institutions'] = array();
        foreach($course->getInstitutions() as $institution)
        {
            $courseDetails['institutions'][] = array(
                'name' => $institution->getName(),
                'url' => $institution->getUrl(),
                'slug' => $institution->getSlug(),
                'isUniversity' => $institution->getIsUniversity(),
                'id' => $institution->getId()
            );
        }

        // Instructors
        $courseDetails['instructors'] = array();
        foreach($course->getInstructors() as $instructor)
        {
            $courseDetails['instructors'][] = $instructor->getName();
        }
        $courseDetails['instructorsSingleLineDisplay'] = $this->getInstructorsSingleLineDisplay($courseDetails['instructors']);

        $courseDetails['schemaOrgs'] = $formatter->getSchemaOrgs();

        // Check if the course has a duplicate course id
        if( $course->getDuplicateCourse() )
        {
            $duplicate = $course->getDuplicateCourse();
            $courseDetails['duplicate'] = array(
                'id' => $duplicate->getId(),
                'slug' => $duplicate->getSlug()
            );
        }

        // Build an array for indepth review
        $indepthReview = array();
        if( $course->getIndepthReview() )
        {
            $ir = $course->getIndepthReview();
            $irUser = $ir->getUser();
            $indepthReview = array(
                'summary' => $ir->getSummary(),
                'rating'   => $ir->getRating(),
                'url'      => $ir->getUrl(),
                'user'     => array(
                    'name' => $irUser->getDisplayName(),
                    'id'   => $irUser->getId(),
                    'handle' => $irUser->getHandle(),
                    'isPrivate' => $irUser->getIsPrivate()
                )
            );
        }
        $courseDetails['indepthReview'] = $indepthReview;

        // Save interview data if exists
        $interview = array();
        if( $course->getInterview() )
        {
            $i = $course->getInterview();
            $interview = array(
                'id' => $i->getId(),
                'title' => $i->getTitle(),
                'summary' => $i->getSummary(),
                'instructorName' => $i->getInstructorName(),
                'instructorPhoto' =>  $i->getInstructorPhoto(),
                'url' => $i->getUrl()
            );
        }
        $courseDetails['interview'] = $interview;

        // Credential details
        // Get the Credential
        $credential = array();
        if ( !$course->getCredentials()->isEmpty() )
        {
            $cred = $course->getCredentials()->first();
            if( $cred->getStatus() < 100 ) // Only if its approved
            {
                $credential['id'] = $cred->getId();
                $credential['name'] = $cred->getName();
                $credential['slug'] = $cred->getSlug();
                $credential['certificateName'] = '';
                $credential['certificateSlug'] = '';

                $formatter = $cred->getFormatter();
                $credential['certificateName'] = $formatter->getCertificateName();
                $credential['certificateSlug'] = $formatter->getCertificateSlug();
            }
        }
        $courseDetails['credential'] = $credential;

        // Merge the extra information with the course object
        $courseDetails['discounted_price'] = 0;
        $courseDetails['discount_percentage'] = 0;
        if($addCourseInfo)
        {
            $courseDetails['price'] = $addCourseInfo['price'];
            $courseDetails['discounted_price'] = $addCourseInfo['discounted_price'];
            $courseDetails['discount_percentage'] = $addCourseInfo['discount_percentage'];

        }

        return $courseDetails;
    }

    /**
     * Generates the url to embed video for youtube videos
     * TODO: Should not be here. Move to an appropriate place
     * @param $videoIntro
     * @return null
     */
    private function  getVideoEmbedUrl($videoIntro)
    {
        if(empty($videoIntro))
        {
            return null;
        }

        $parsedUrl = parse_url($videoIntro);
        if (!isset($parsedUrl['query']))
        {
            return null;
        }
        parse_str($parsedUrl['query'], $getParams);
        if(isset($getParams['v']))
        {
            return 'https://www.youtube.com/embed/' .  $getParams['v'] . '?wmode=transparent';
        }

        return null;
    }

    /**
     * Formats the instructors so that it can be displayed in a single line display
     * TODO: Should not be here. Move to an appropriate place
     *
     */
    private function getInstructorsSingleLineDisplay($instructors = array())
    {

        switch(count($instructors))
        {
            case 0:
                return '';
                break;
            case 1:
                return array_pop($instructors);
                break;
            case 2:
                return  implode(' and ',$instructors);
                break;
            default:
                // More than 2 elements
                $last = array_pop($instructors);
                $str = implode($instructors, ', ');

                return $str. ' and ' . $last;
                break;

        }
    }

    /**
     * Retrieves new courses since the given date
     * @param \DateTime $dt
     */
    public function getNewCourses(\DateTime $dt)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query
            ->add('select','c')
            ->add('from','ClassCentralSiteBundle:Course c')
            ->add('where','c.created >= :date AND c.status = '. CourseStatus::AVAILABLE)
            ->setParameter('date', $dt->format("Y-m-d"));

        return $query->getQuery()->getResult();
    }

    /**
     * Gets the count of number of times the course has been
     * added to the users list
     */
    public function getListedCount( Course $course)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query
            ->add('select', 'count(uc.id) as listed')
            ->add('from', 'ClassCentralSiteBundle:UserCourse uc')
            ->join('uc.course','c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $course->getId())
            ;

        $listed = $query->getQuery()->getSingleScalarResult();

        return $listed;
    }

    /**
     * Returns a list of users interested in a particular course
     * @param Course $course
     */
    public function getInterestedUsers( $id )
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query
            ->add('select', 'u.id as id, u.name as name, u.handle as handle, p.location as location, p.aboutMe as aboutMe')
            ->add('from', 'ClassCentralSiteBundle:User u')
            ->join('u.userCourses','uc')
            ->leftJoin('u.profile','p')
            ->andWhere('uc.course = :id')
            ->andWhere('u.isPrivate = 0')
            ->orderBy('p.score','DESC')
            ->setParameter('id', $id)
            ;
        return $query->getQuery()->getResult( Query::HYDRATE_ARRAY );
    }
}