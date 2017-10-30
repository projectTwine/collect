<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 4/30/15
 * Time: 7:08 PM
 */

namespace ClassCentral\ScraperBundle\Scraper\Udacity;


use ClassCentral\CredentialBundle\Entity\Credential;
use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Services\Kuber;

class Scraper extends ScraperAbstractInterface{

    const COURSES_API_ENDPOINT = 'https://www.udacity.com/public-api/v0/courses';

    private $courseFields = array(
        'Url', 'Description', 'DurationMin','DurationMax', 'Name','LongDescription','Certificate','VideoIntro', 'Syllabus',
        'WorkloadMin','WorkloadMax','WorkloadType'
    );

    private $credentialFields = array(
        'Url','Description','Name', 'SubTitle'
    );

    private $offeringFields = array(
       'Url'
    );

    public static $credentialSlugs = array(
        'ruby-programming-nanodegree--nd010' => 'beginning-ruby-nanodegree--nd010',
        'beginning-ios-app-development-nanodegree--nd006' => 'beginning-ios-app-development--nd006'
    );

    public function scrape()
    {
        if($this->isCredential)
        {
            $this->scrapeCredentials();
            return;
        }

        $em = $this->getManager();
        $udacityCourses = json_decode( file_get_contents(self::COURSES_API_ENDPOINT), true );
        $courseService = $this->container->get('course');
        $coursesChanged = array();

        foreach ($udacityCourses['courses'] as $udacityCourse)
        {
            $course = $this->getCourseEntity( $udacityCourse );
            $offering = null;
            $dbCourse = $this->dbHelper->getCourseByShortName( $course->getShortName() );
            if( !$dbCourse )
            {
                $dbCourse = $this->dbHelper->findCourseByName( $course->getName() , $this->initiative );
            }

            if( !$dbCourse )
            {
              
                // Course does not exist create it.
                if($this->doCreate())
                {
                    $this->out("NEW COURSE - " . $course->getName());

                    // NEW COURSE
                    if ($this->doModify())
                    {
                        $em->persist($course);
                        $em->flush();

                        $this->dbHelper->sendNewCourseToSlack( $course, $this->initiative );

                        if( $udacityCourse['banner_image'] )
                        {
                            $courseService->uploadImageIfNecessary( $udacityCourse['banner_image'], $course);
                        }


                        // Create new offering
                        $offering = new Offering();
                        $offering->setCourse( $course );
                        $offering->setUrl( $course->getUrl() );

                        $startDate = new \DateTime();
                        $offering->setStartDate( $startDate );

                        $endDate = new \DateTime();
                        $endDate->add( new \DateInterval('P30D'));
                        $offering->setEndDate( $endDate );

                        $offering->setStatus( Offering::COURSE_OPEN );

                        $em->persist( $offering );
                        $em->flush();
                    }
                    $courseChanged = true;
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

                        if( $udacityCourse['banner_image'] )
                        {
                            $courseService->uploadImageIfNecessary( $udacityCourse['banner_image'], $course);
                        }
                    }
                    $courseChanged = true;
                }

                // Check if offering has been modified
                $offering = $dbCourse->getNextOffering();
                if($offering->getUrl() != $course->getUrl() && $this->doUpdate() )
                {
                    $this->out("UPDATE COURSE - " . $dbCourse->getName() . " - ". $dbCourse->getId());
                    // Offering modified
                    $this->outputChangedFields( array( array(
                        'field' => 'offering Url',
                        'old' => $offering->getUrl(),
                        'new' => $course->getUrl()

                    ) ) );

                    if ($this->doModify())
                    {
                        $offering->setUrl( $course->getUrl() );
                        $em->persist( $offering );
                        $em->flush();
                    }
                    $courseChanged = true;
                }


                $course = $dbCourse;


            }
        }

        if( $courseChanged )
        {
            $coursesChanged[] = $course;
        }

        return $coursesChanged;
    }

    private function getCourseEntity( $udacityCourse = array() )
    {
        $defaultStream = $this->dbHelper->getStreamBySlug('cs');
        $langMap = $this->dbHelper->getLanguageMap();
        $defaultLanguage = $langMap[ 'English' ];

        $course = new Course();
        $course->setShortName( substr('udacity_' . $udacityCourse['slug'],0,50));
        $course->setInitiative( $this->initiative );
        $course->setName( $udacityCourse['title'] );
        $course->setDescription( $udacityCourse['short_summary'] );
        $course->setLanguage( $defaultLanguage);
        $course->setStream($defaultStream); // Default to Computer Science
        $course->setCertificate( false );
        $course->setUrl( $udacityCourse['homepage'] );
        $course->setSyllabus( nl2br($udacityCourse['syllabus']) );
        $course->setWorkloadMin( 6 ) ;
        $course->setWorkloadMax( 6 ) ;
        $course->setWorkloadType(Course::WORKLOAD_TYPE_HOURS_PER_WEEK);
        ;

        // Calculate length
        $length = null;
        $expectedDuration = $udacityCourse['expected_duration'];
        if( $udacityCourse['expected_duration_unit'] == 'months')
        {
            $length = $expectedDuration * 4;
        }
        elseif ($udacityCourse['expected_duration_unit'] == 'weeks')
        {
            $length = $expectedDuration;
        }
        $course->setDurationMin($length);
        $course->setDurationMax($length);

        // Calculate Description
        $course->setLongDescription( nl2br($udacityCourse['summary'] . '<br/><br/><b>Why Take This Course?</b><br/>' .  $udacityCourse['expected_learning']));

        // Intro Video
        if( !empty($udacityCourse['teaser_video']['youtube_url']) )
        {
            $course->setVideoIntro( $udacityCourse['teaser_video']['youtube_url'] );
        }


        return $course;
    }

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

    public function scrapeCredentials()
    {
        $data = json_decode( file_get_contents(self::COURSES_API_ENDPOINT), true );
        foreach($data['degrees'] as $nanodegree)
        {
            $credential = $this->getCredentialFromNanodegree( $nanodegree );
            $this->saveOrUpdateCredential( $credential, $nanodegree['image'] );
        }
    }

    public function getCredentialFromNanodegree( $nanodegree )
    {
        $credential = new Credential();

        $credential->setName( $nanodegree['title'] );
        $credential->setPricePeriod( Credential::CREDENTIAL_PRICE_PERIOD_MONTHLY);
        $credential->setPrice(200);
        if (isset(self::$credentialSlugs[ $nanodegree['slug']]))
        {
            $nanodegree['slug'] = self::$credentialSlugs[ $nanodegree['slug']];
        }
        $credential->setSlug( $nanodegree['slug'] );
        $credential->setInitiative( $this->initiative );
        $credential->setUrl( $nanodegree['homepage'] );
        $credential->setOneLiner( $nanodegree['short_summary'] );
        $credential->setSubTitle( $nanodegree['subtitle'] );
        $credential->setWorkloadMax(10);
        $credential->setWorkloadMin(10);
        $credential->setWorkloadType(Credential::CREDENTIAL_WORKLOAD_TYPE_HOURS_PER_WEEK);
        $credential->setDurationMax( $nanodegree['expected_duration'] );
        $credential->setDurationMin( $nanodegree['expected_duration'] );

        // Collect the description
        $summary = $nanodegree['summary'];
        $expectedLearning = $nanodegree['expected_learning'];
        $requiredKnowledge = $nanodegree['required_knowledge'];

        $credential->setDescription(
            "<p>$summary</p>" .
            "<h3 class='table-tab-content__title'>Why Take This Nanodegree?</h3>".
            "<p>$expectedLearning</p>".
            "<h3 class='table-tab-content__title'>Required Knowledge</h3>".
            "<p>$requiredKnowledge</p>"
        );

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

                    $this->dbHelper->uploadCredentialImageIfNecessary($imageUrl,$credential,'png');
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
                // Update the credential
                if ($this->doModify())
                {
                    $em->persist($dbCredential);
                    $em->flush();

                    $this->dbHelper->uploadCredentialImageIfNecessary($imageUrl,$dbCredential,'png');
                }
            }


        }
    }
}