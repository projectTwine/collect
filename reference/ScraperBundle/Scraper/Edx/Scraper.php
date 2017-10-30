<?php

namespace ClassCentral\ScraperBundle\Scraper\Edx;

use ClassCentral\CredentialBundle\Entity\Credential;
use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Utility\UniversalHelper;

class Scraper extends ScraperAbstractInterface
{
    const BASE_URL = "https://www.edx.org";
    const COURSE_CATALOGUE = "https://www.edx.org/course-list/allschools/allsubjects/allcourses";
    const EDX_COURSE_LIST_CSV = "https://www.edx.org/api/report/course-feed/export";
    const EDX_RSS_API = "https://www.edx.org/api/v2/report/course-feed/rss?page=%s";
    const EDX_CARDS_API = "https://www.edx.org/api/discovery/v1/course_run_cards";
    const EDX_ENROLLMENT_COURSE_DETAIL = 'https://courses.edx.org/api/enrollment/v1/course/%s?include_expired=1'; // Contains pricing information
    const EDX_API_ALL_COURSES_BASE_v1 = 'https://api.edx.org';
    const EDX_API_ALL_COURSES_PATH_v1 = '/catalog/v1/catalogs/11/courses/';

    const EDX_DRUPAL_CATALOG = 'https://www.edx.org/api/v1/catalog/search?page_size=50&partner=edx&content_type[]=courserun&page=';
    const EDX_DRUPAL_INDIVIDUAL = 'https://www.edx.org/api/catalog/v2/courses/%s';
    const EDX_DRUPAL_COURSE_RUNS =  'https://www.edx.org/api/v1/catalog/course_runs/%s'; // contains uuids required for sessions/offerings
    const EDX_DRUPAL_COURSE_MODES = 'https://courses.edx.org/api/enrollment/v1/course/%s'; // contains pricing ino

    public STATIC $EDX_XSERIES_GUID = array(15096, 7046, 14906,14706,7191, 13721,13296, 14951, 13251,15861, 15381
        ,15701, 7056
    );
    private $credentialFields = array(
        'Url','Description','Name', 'OneLiner', 'SubTitle'
    );


    private $courseFields = array(
        'Url', 'Description', 'Name','LongDescription','VideoIntro','Certificate',
        'CertificatePrice','ShortName','Syllabus','IsMooc'
    );

    private $offeringFields = array(
        'StartDate', 'EndDate', 'Url','ShortName','Status'
    );

    private $subjectsMap = array(
        'Computer Science' => 'cs',
        'Data Analysis & Statistics' => 'data-science',
        'Biology & Life Sciences' => 'biology',
        'Education & Teacher Training' => 'education',
        'Engineering' => 'engineering',
        'Economics & Finance' => 'economics',
        'Science' => 'science',
        'Social Sciences' => 'social-sciences',
        'Physics' => 'Physics',
        'Business & Management' => 'business',
        'Humanities' => 'humanities',
        'Law' => 'law',
        'History' => 'history',
        'Communication' => 'communication-skills',
        'Literature' => 'literature',
        'Math' => 'maths',
        'Food & Nutrition' =>'nutrition-and-wellness',
        'Art & Culture' => 'art-and-design',
        'Chemistry' => 'chemistry',
        'Health & Safety' => 'health',
        'Philosophy & Ethics' => 'philosophy',
        'Language' => 'language-culture',
        'Music' => 'music',
        'Electronics' => 'electrical-engineering',
        'Design' => 'art-and-design',
        'Environmental Studies' => 'environmental-science',
        'Medicine' => 'health',
        'Architecture' => 'visual-arts',
        'Energy & Earth Sciences' => 'environmental-science',
        'Ethics' => 'social-sciences',
    );

    private $sleepMultiplier = 1;

    private $coursesWithSameName = array('Introduction to Differential Equations');

    /**
     * Using the CSV
     */
    public function scrape()
    {

        if($this->isCredential)
        {
            $this->scrapeCredentials();
            return;
        }

        $tagService = $this->container->get('tag');
        $courseService = $this->container->get('course');
        $em = $this->getManager();

        /**
         * The edX.org drupal API used to render courses pages and course catalog
         */
        // Build the catalog.
        $edxCourses = $this->getEdxDrupalJson();
        $duplicateOfferings = 0;
        foreach($edxCourses as $edxCourse)
        {
            $course =  null;
            try
            {
                $course =  $this->getCourseEntityFromDrupalAPI($edxCourse);
            }
            catch (\Exception $e)
            {
                $this->out("Error creating Course Entity for course : ".$edxCourse['title'] );
                continue; // Skip this course
            }


            $cTags = array();
            foreach( $edxCourse['course_page_info']['schools']  as $school)
            {
                $cTags[] = strtolower($school['title']);
            }

            $dbCourse = $this->dbHelper->getCourseByShortName( $course->getShortName() );

            // Do a fuzzy match on the course title. Don't match for courses with same names
            if (!$dbCourse && !in_array($course->getName(),$this->coursesWithSameName))
            {
                $result = $this->findCourseByName( $edxCourse['title'], $this->initiative);
                if( count($result) > 1)
                {
                    $this->out("DUPLICATE ENTRIES FOR: " . $edxCourse['title']);
                    foreach ($result as $item)
                    {
                        $this->out( "COURSE ID" . $item->getId() );
                    }
                    continue;
                }
                else if (count($result) == 1)
                {
                    $dbCourse = $result;
                }
            }

            if( !$dbCourse )
            {

                if($this->doCreate())
                {
                    $this->out("NEW COURSE - " . $course->getName());
                    // NEW COURSE
                    if ($this->doModify())
                    {
                        if(!empty($edxCourse['course_page_info']['staff']))
                        {
                            foreach( $edxCourse['course_page_info']['staff'] as $staff )
                            {
                                $insName = $staff['title'];
                                if(!empty($insName))
                                {
                                    $course->addInstructor($this->dbHelper->createInstructorIfNotExists($insName));
                                }
                            }
                        }


                        $em->persist($course);
                        $em->flush();

                        $tagService->saveCourseTags( $course, $cTags);

                        $this->dbHelper->sendNewCourseToSlack( $course, $this->initiative );

                        if( $edxCourse['course_page_info']['image'] )
                        {
                            $courseService->uploadImageIfNecessary( $edxCourse['course_page_info']['image'], $course);
                        }

                    }
                }
            }
            else
            {
                // Check if any fields are modified
                $courseModified = false;
                $changedFields = array(); // To keep track of fields that have changed
                foreach($this->courseFields as $field)
                {
                    $getter = 'get' . $field;
                    $setter = 'set' . $field;
                    if($course->$getter() != $dbCourse->$getter())
                    {
                        $courseModified = true;

                        // Add the changed field to the changedFields array
                        $changed = array();
                        $changed['field'] = $field;
                        $changed['old'] =$dbCourse->$getter();
                        $changed['new'] = $course->$getter();
                        $changedFields[] = $changed;

                        $dbCourse->$setter($course->$getter());
                    }

                }

                if($courseModified && $this->doUpdate())
                {
                    //$this->out( "Database course changed " . $dbCourse->getName());
                    // Course has been modified
                    $this->out("UPDATE COURSE - " . $dbCourse->getName() . " - ". $dbCourse->getId());
                    $this->outputChangedFields($changedFields);
                    if ($this->doModify())
                    {
                        $em->persist($dbCourse);
                        $em->flush();

                        // Update tags
                        $tagService->saveCourseTags( $dbCourse, $cTags);
                    }

                }

                if( $this->doUpdate() && $edxCourse['course_page_info']['image'] )
                {
                    $courseService->uploadImageIfNecessary( $edxCourse['course_page_info']['image'], $dbCourse);
                }

                $course = $dbCourse;

            }

            /***************************
             * CREATE OR UPDATE OFFERING
             ***************************/

            // Check if the course run is not empty
            if(empty($edxCourse['course_runs']))
            {
                continue;
            }

            $offering = $this->getOffering($edxCourse,$course);

            $dbOffering = $this->dbHelper->getOfferingByShortName($offering->getShortName());

            // figure out if its a duplicate
            if(!$dbOffering)
            {
                foreach($course->getOfferings() as $off)
                {
                    if( $off->getStartDate()->format('Y-m') == $offering->getStartDate()->format('Y-m') )
                    {
                        $dbOffering = $off;
                        $duplicateOfferings++;
                        break;
                    }
                }
            }

            if (!$dbOffering)
            {
                if($this->doCreate())
                {
                    $this->out("NEW OFFERING - " . $offering->getName());
                    if ($this->doModify())
                    {
                        $em->persist($offering);
                        $em->flush();
                    }
                    $this->dbHelper->sendNewOfferingToSlack( $offering);
                    $offerings[] = $offering;
                }
            }
            else
            {
                // old offering. Check if has been modified or not
                $offeringModified = false;
                $changedFields = array();
                foreach ($this->offeringFields as $field)
                {
                    $getter = 'get' . $field;
                    $setter = 'set' . $field;

                    $valueOffering = $offering->$getter();
                    $valueDbOffering = $dbOffering->$getter();

                    if($field == 'StartDate' || $field =='EndDate')
                    {
                        $valueOffering = $valueOffering->format('Y-m-d');
                        $valueDbOffering = $valueDbOffering->format('Y-m-d');
                    }
                    if ($valueOffering != $valueDbOffering )
                    {
                        $offeringModified = true;
                        // Add the changed field to the changedFields array
                        $changed = array();
                        $changed['field'] = $field;
                        $changed['old'] =$dbOffering->$getter();
                        $changed['new'] = $offering->$getter();
                        $changedFields[] = $changed;
                        $dbOffering->$setter($offering->$getter());
                    }
                }

                if ($offeringModified && $this->doUpdate())
                {
                    // Offering has been modified
                    $this->out("UPDATE OFFERING - " . $dbOffering->getName());
                    $this->outputChangedFields($changedFields);
                    if ($this->doModify())
                    {
                        $em->persist($dbOffering);
                        $em->flush();
                    }
                    $offerings[] = $dbOffering;

                }
            }
        }

        $this->out(  $duplicateOfferings );

        return;

    }

    /**
     * Given an array built from edX csv returns a course entity
     * @param array $c
     */
    private function getCourseEntityFromDrupalAPI ($c = array())
    {

        $langMap = $this->dbHelper->getLanguageMap();
        $language = $langMap[ 'English' ];
        $edXLanguage = $c['language'];
        if($edXLanguage == 'Chinese - Mandarin')
        {
            $edXLanguage ='Chinese';
        }
        if( isset($langMap[$edXLanguage]))
        {
            $language = $langMap[$edXLanguage];
        }
        $stream = $this->dbHelper->getStreamBySlug('cs');

        if(!empty($c['course_page_info']['subjects']))
        {
            foreach($c['course_page_info']['subjects'] as $sub)
            {
                if(isset($this->subjectsMap[ $sub['title'] ]))
                {
                    $stream = $this->dbHelper->getStreamBySlug( $this->subjectsMap[ $sub['title'] ] );
                }
                break;
            }
        }

        if($c['number'] =='CS50')
        {
            // CS50x and CS50AP both have the same number. So use the display_course_number
            $c['number'] = $c['course_page_info']['display_course_number'];
        }

        $shortName = strtolower('edx_'.$c['number'].'_'.$c['org']);

        $course = new Course();
        $course->setShortName( $shortName );
        $course->setInitiative( $this->initiative );
        $course->setIsMooc(true);
        $course->setName(  $c['title'] );
        $course->setDescription( $c['course_page_info']['subtitle'] );
        $course->setLongDescription( $c['course_page_info']['description'] );
        $course->setSyllabus( $c['course_page_info']['syllabus']);
        $course->setLanguage( $language);
        $course->setStream($stream); // Default to Computer Science
        $course->setUrl($c['marketing_url']);
        $course->setCertificate( false );
        $course->setCertificatePrice( 0 );

        foreach($c['course_page_info']['subjects'] as $sub)
        {
            if(isset($this->subjectsMap[ $sub['title'] ]))
            {
                $stream = $this->dbHelper->getStreamBySlug( $this->subjectsMap[ $sub['title'] ] );
            }
            break;
        }

        if(!empty($c['course_page_info']['video']['url']))
        {
            $course->setVideoIntro($c['course_page_info']['video']['url'] );
        }
        // Check if the video is in course runs
        if (isset($c['course_modes']['course_modes']))
        {
            foreach($c['course_modes']['course_modes'] as $courseMode)
            {

                if($courseMode['slug'] == 'verified')
                {
                    $course->setCertificatePrice( $courseMode['min_price'] );
                    $course->setCertificate( true );
                }
            }
        }

        // the course is archived. available to register, but certificates not available.
        if($c['availability'] =='Archived')
        {
            $course->setCertificate( false );
            $course->setCertificatePrice( 0 );
        }

        if(isset($c['course_runs']) && $c['course_runs']['type'] =='professional')
        {
            $course->setIsMooc(false);
            $course->setPrice(  $course->getCertificatePrice() );
            $course->setPricePeriod( Course::PRICE_PERIOD_TOTAL );
        }

        foreach( $c['course_page_info']['schools']  as $school)
        {
            $ins = $this->dbHelper->getInstitutionByName( trim($school['name']) );
            if($ins)
            {
                $course->addInstitution( $ins );
            }
        }

        return $course;
    }

    private function getOffering($c, Course $course)
    {
        $offering = new Offering();
        $now = new \DateTime();
        $run = $c['course_runs'];
        $offering->setShortName( 'edx_' . $run['uuid'] );
        $offering->setCourse( $course );
        $offering->setUrl( $c['marketing_url'] );
        $offering->setStatus( Offering::START_DATES_KNOWN );
        $offering->setStartDate( new \DateTime( $c['start'] ) );
        if(  !empty($c['end']) )
        {
            $offering->setEndDate(  new \DateTime(  $c['end'] ) );
        }
        else if( !empty($c['course_page_info']['end']) )
        {
            $offering->setEndDate(  new \DateTime(  $c['course_page_info']['end'] ) );
        }


        if($run['pacing_type'] == 'instructor_paced')
        {
            // Do nothing
        }
        elseif($run['pacing_type'] == 'self_paced')
        {
            if($now > $offering->getStartDate())
            {
                $offering->setStatus( Offering::COURSE_OPEN );
            }
            else
            {
                $offering->setStatus( Offering::START_DATES_KNOWN );
            }
        }

        // the course is archived. available to register, but certificates not available.
        if($c['availability'] =='Archived')
        {
            $offering->setStatus( Offering::COURSE_OPEN );
        }

        if( !empty($c['enrollment_end']) )
        {
            $enrollmentEndDate = new \DateTime($c['enrollment_end']);

            if( ($now > $enrollmentEndDate) && $offering->getStatus() == Offering::COURSE_OPEN)
            {
                // This will make the course show not as self paced.
                $offering->setStatus( Offering::START_DATES_KNOWN );
                $this->out( "Offering is not self paced: " .$course->getName());
            }
        }

        return $offering;
    }

    /**
     * Used to print the field values which have been modified for both offering and courses
     * @param $changedFields
     */
    private function outputChangedFields($changedFields)
    {
        foreach($changedFields as $changed)
        {
            $field = $changed['field'];
            $old = is_a($changed['old'], 'DateTime') ? $changed['old']->format('jS M, Y') : $changed['old'];
            $new = is_a($changed['new'], 'DateTime') ? $changed['new']->format('jS M, Y') : $changed['new'];

            $this->out("$field changed from - '$old' to '$new'");
        }
    }

    /**
     * Tries to find a edx course with the particular title
     * @param $title
     * @param $initiative
     */
    private function findCourseByName ($title, Initiative $initiative)
    {
        $em = $this->getManager();
        $result = $em->getRepository('ClassCentralSiteBundle:Course')->createQueryBuilder('c')
                    ->where('c.initiative = :initiative' )
                    ->andWhere('c.name LIKE :title')
                    ->setParameter('initiative', $initiative)
                    ->setParameter('title', '%'.$title)
                    ->getQuery()
                    ->getResult()
        ;

        if ( count($result) == 1)
        {
            return $result[0];
        }

        return null;
    }

    public function scrapeCredentials()
    {

        foreach(self::$EDX_XSERIES_GUID as $guid)
        {
            $xseries = json_decode($this->file_get_contents_wrapper(
                sprintf( 'https://www.edx.org/node/%s.json?deep-load-refs=1',$guid )),
                true);
            $credential = $this->getCredential($xseries);
            $this->saveOrUpdateCredential( $credential, $xseries['field_xseries_banner_image']['file']['uri'] );
        }

        /**
        $edXCourses = json_decode(file_get_contents( 'https://www.edx.org/search/api/all' ),true);
        foreach($edXCourses as $edXCourse)
        {
             if(in_array('xseries',$edXCourse['types']))
             {
                $this->out( $edXCourse['l'] );
                 $guid = $edXCourse['guid'];
                 var_dump($guid);
                 continue;
                 $xseries = json_decode(file_get_contents(
                     sprintf( 'https://www.edx.org/node/%s.json?deep-load-refs=1',$guid )),
                     true);



             }
        }

        return;

        $edXCourses = json_decode(file_get_contents( self::EDX_CARDS_API ),true);
        foreach($edXCourses as $edXCourse)
        {
            if( isset($edXCourse['attributes']['xseries'] ) )
            {
                $this->out($edXCourse['title']);
                $xseriesId = $edXCourse['attributes']['xseries'];

                var_dump(  $edXCourse['attributes'] );
            }
        }
         */
    }


    public function getCredential( $xseries )
    {
        $credential = new Credential();
        $credential->setName( $xseries['title'] );
        $credential->setPricePeriod(Credential::CREDENTIAL_PRICE_PERIOD_TOTAL);
        $credential->setPrice(0);
        $credential->setSlug( UniversalHelper::getSlug( $credential->getName()) . '-xseries'   );
        $credential->setInitiative( $this->initiative );
        $credential->setUrl( $xseries['url'] );
        $credential->setOneLiner( $xseries['field_xseries_subtitle'] );
        $credential->setSubTitle( $xseries['field_xseries_subtitle_short'] );
        $credential->setDescription( $xseries['body']['value'] .  $xseries['field_xseries_overview']['value'] );

        return $credential;
    }

    /**
     * @param Credential $credential
     */
    private function saveOrUpdateCredential(Credential $credential, $imageUrl)
    {
        $dbCredential = $this->dbHelper->getCredentialBySlug( $credential->getSlug() ) ;
        $em = $this->getManager();
        if( !$dbCredential )
        {
            if($this->doCreate())
            {
                $this->out("New Credential - " . $credential->getName() );
                if ($this->doModify())
                {
                    $em->persist( $credential );
                    $em->flush();

                    $this->dbHelper->uploadCredentialImageIfNecessary($imageUrl,$credential);
                }
            }
        }
        else
        {
            // Update the credential
            $changedFields = $this->dbHelper->changedFields($this->credentialFields,$credential,$dbCredential);
            if(!empty($changedFields) && $this->doUpdate())
            {
                $this->out("UPDATE CREDENTIAL - " . $dbCredential->getName() );
                $this->outputChangedFields( $changedFields );
                if ($this->doModify())
                {
                    $em->persist($dbCredential);
                    $em->flush();

                    $this->dbHelper->uploadCredentialImageIfNecessary($imageUrl,$dbCredential);
                }
            }

        }
    }

    public function getEdxDrupalJson()
    {
        $today = new \DateTime();
        $today = $today->format('Y_m_d');

        $filename = "edx_$today.json";
        $filePath = '/tmp/'.$filename;
        $edXCourses = array();
        if(file_exists($filePath))
        {

            $edXCourses = json_decode($this->file_get_contents_wrapper($filePath),true);
            $this->out("Read from cache");
        }
        else
        {
            $currentPage=1;
            do
            {
                $this->out("Page No. " . $currentPage);
                $allCourses =  json_decode( $this->file_get_contents_wrapper(self::EDX_DRUPAL_CATALOG . $currentPage),true);
                foreach($allCourses['objects']['results'] as $edXCourse)
                {
                    $this->out($edXCourse['title']);
                    $edXCourse['course_page_info'] =  json_decode( $this->file_get_contents_wrapper(sprintf(self::EDX_DRUPAL_INDIVIDUAL,$edXCourse['key'])),true);
                    $edXCourse['course_runs'] = json_decode( $this->file_get_contents_wrapper(sprintf(self::EDX_DRUPAL_COURSE_RUNS,$edXCourse['key'])),true);
                    $edXCourse['course_modes'] = json_decode( $this->file_get_contents_wrapper(sprintf(self::EDX_DRUPAL_COURSE_MODES,$edXCourse['key'])),true);

                    $edXCourses[] = $edXCourse;
                }
                $currentPage++;
            }  while( $allCourses['objects']['next'] );

            file_put_contents($filePath,json_encode($edXCourses));
        }
        return $edXCourses;
    }

    /**
     * Wrapper to catch exceptions in file_get_contents
     * @param $url
     */
    private function file_get_contents_wrapper($url)
    {
        try
        {
            $contents =file_get_contents($url);
            // successfully completed the call. Return it back to one. Return sleepmultipler time back to 1.
            $this->sleepMultiplier = 1;

            return $contents;
        } catch (\Exception $e)
        {
            $this->out("file_get_contents_error: " . $e->getMessage());
            $msg = $e->getMessage();
            if(strpos($msg,'429') !== false)
            {

                $sleepTime = 10*$this->sleepMultiplier;
                $this->out("Too many requests - Going to Sleep right now for $sleepTime seconds");
                sleep($sleepTime);
                $this->sleepMultiplier++; // If it happens again increase the time to sleep.
                $this->file_get_contents_wrapper($url);
            }
            elseif("SSL: Connection reset by peer" == $e->getMessage())
            {
                return array();
            }
        }

        return '';
    }


}