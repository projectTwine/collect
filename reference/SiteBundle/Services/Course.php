<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 4/29/16
 * Time: 11:02 PM
 */

namespace ClassCentral\SiteBundle\Services;


use Guzzle\Http\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ClassCentral\SiteBundle\Entity\Course as CourseEntity;

class Course
{
    private $container;

    public static $UDEMY_API_URL = 'https://www.udemy.com/api-2.0';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function uploadImageIfNecessary( $imageUrl, CourseEntity $course)
    {
        $kuber = $this->container->get('kuber');
        $uniqueKey = basename($imageUrl);
        if(strpos($uniqueKey,'?'))
        {
            $uniqueKey = reset(explode('?', $uniqueKey));
        }
        if( $kuber->hasFileChanged( Kuber::KUBER_ENTITY_COURSE,Kuber::KUBER_TYPE_COURSE_IMAGE, $course->getId(),$uniqueKey ) )
        {
            // Upload the file
            $filePath = '/tmp/course_'.$uniqueKey;
            file_put_contents($filePath,file_get_contents($imageUrl));
            $kuber->upload(
                $filePath,
                Kuber::KUBER_ENTITY_COURSE,
                Kuber::KUBER_TYPE_COURSE_IMAGE,
                $course->getId(),
                null,
                $uniqueKey
            );

        }
    }

    // Used for spotlight section
    public function getRandomPaidCourse()
    {
        $finder = $this->container->get('course_finder');
        $results = $finder->paidCourses();
        $courses = array();
        foreach($results['hits']['hits'] as $course)
        {
            $courses[] = $course['_source'];
        }

        $index = rand(0,count($courses)-1);

        return $courses[$index];
    }

    public function getRandomPaidCourseByProvider($providerName)
    {
        $finder = $this->container->get('course_finder');
        $results = $finder->byProvider('treehouse');

        $courses = array();
        foreach($results['hits']['hits'] as $course)
        {
            $courses[] = $course['_source'];
        }

        $index = rand(0,count($courses)-1);
        return $courses[$index];
    }

    public function getRandomPaidCourseExcludeByProvider($providerName)
    {
        $finder = $this->container->get('course_finder');
        $results = $finder->paidCourses();

        $courses = array();
        foreach($results['hits']['hits'] as $course)
        {
            if($course['_source']['provider']['name'] != $providerName)
            {
                $courses[] = $course['_source'];
            }
        }

        $index = rand(0,count($courses)-1);
        return $courses[$index];
    }

    public function getCourseImage(CourseEntity $course)
    {
        return $this->getCourseImageFromId($course->getId());
    }

    public function getCourseImageFromId($courseId)
    {
        $kuber = $this->container->get('kuber');
        $url = $kuber->getUrl( Kuber::KUBER_ENTITY_COURSE ,Kuber::KUBER_TYPE_COURSE_IMAGE, $courseId );
        return $url;
    }


    /**
     * Get additional info for the courses from json file
     */
    public function getCoursesAdditionalInfo()
    {
        $filePath = $this->container->get('kernel')->getRootDir(). '/../extras/add_course_info.json';
        $coursesJson = file_get_contents($filePath);
        if($coursesJson)
        {
            $courses = json_decode($coursesJson,true);
            return $courses;
        }

        return array();
    }

    /**
     * Gets additional information for a specific course
     * @param CourseEntity $course
     * @return array
     */
    public function getCourseAdditionalInfo(CourseEntity $course)
    {
        $coursesInfo = self::getCoursesAdditionalInfo();
        if(!empty($coursesInfo[$course->getId()]))
        {
            return $coursesInfo[$course->getId()];
        }

        return array();
    }

    /**
     * Gets the collection json
     * @param $slug
     */
    public function getCollection($slug)
    {
        $filePath = $this->container->get('kernel')->getRootDir(). '/../extras/collection.json';
        $coursesJson = file_get_contents($filePath);
        if($coursesJson)
        {
            $courses = json_decode($coursesJson,true);
            if(isset($courses[$slug]))
            {
                return $courses[$slug];
            }
        }

        return array();
    }

    public function getOldStackCourse($courseId)
    {
        $filePath = $this->container->get('kernel')->getRootDir(). '/../extras/coursera_old_stack.json';
        $coursesJson = file_get_contents($filePath);
        if($coursesJson)
        {
            $courses = json_decode($coursesJson,true);
            if(isset($courses[$courseId]))
            {
                return $courses[$courseId];
            }
        }
        return false;
    }

    /**
     * Get Udemy courses
     * $options contains fields mentioned here: https://www.udemy.com/developers/methods/get-courses-list/
     * @param $courses
     */
    public function getUdemyCourses($options = array())
    {
        $client = new Client();
        $clientId = $this->container->getParameter('udemy_client_id');
        $clientSecret = $this->container->getParameter('udemy_client_secret');
        $credentials = base64_encode("$clientId:$clientSecret");
        $query = http_build_query($options);
        $request =  $client->get(self::$UDEMY_API_URL . '/courses?'. $query, [
            'Authorization' => ['Basic '.$credentials]
        ]);

        $response = $request->send();

        if($response->getStatusCode() !== 200)
        {
            return array();
        }

        return json_decode($response->getBody(true),true);
    }

    /**
     * Given an array of institutions, it returns the courses taught by this institution.
     * @param array $institutions
     */
    public function getCourseIdsFromInstitutions($institutions = array())
    {
        $institutions = implode(',',$institutions);
        $conn = $this->container->get('doctrine')->getManager()->getConnection();
        $statement = $conn->prepare("SELECT course_id FROM courses_institutions WHERE institution_id in ($institutions);");
        $statement->execute();
        $results = $statement->fetchAll();
        $courseIds = array();
        foreach ($results as $result)
        {
            $courseIds[] = $result['course_id'];
        }

        return $courseIds;
    }
}