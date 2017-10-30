<?php
namespace ClassCentral\ScraperBundle\Scraper\Eduopen;

use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;

class Scraper extends ScraperAbstractInterface
{

    const COURSES_API = 'https://learn.eduopen.org/rss/courses.php';
    const COURSE_PAGE_URL = 'https://learn.eduopen.org/eduopen/course_details.php?courseid=';

    private $courseFields = array(
        'Url', 'Description', 'DurationMin','DurationMax','Name','LongDescription','Certificate','WorkloadType','CertificatePrice'
    );

    private $offeringFields = array(
        'StartDate', 'EndDate', 'Url', 'Status'
    );

    private $subjectsMap = array(
        'Science' => 'science',
        'Computer and Data Sciences' => 'cs',
        'Social Science' => 'social-sciences',
        'Social Science (Archived)' =>'social-sciences',
        'Health and Pharmacology' => 'health',
        'Computer and Data Sciences (Archived)' =>'cs',
        'Arts and Humanities' => 'humanities',
        'Arts and Humanities (Archived)' => 'humanities',
        'Technology, Design and Engineering' => 'engineering',
    );


    public function scrape()
    {
        if($this->isCredential)
        {
            // $this->scrapeCredentials();
            return;
        }

        $em = $this->getManager();
        $courseService = $this->container->get('course');

        $eduOpenCourses = json_decode(file_get_contents( self::COURSES_API ), true );
        foreach($eduOpenCourses as $eduOpenCourse)
        {
            $course = $this->getCourseEntity( $eduOpenCourse );
            $dbCourse = $this->dbHelper->getCourseByShortName( $course->getShortName() );

            if(!$dbCourse)
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

                        if( $eduOpenCourse['course_image_url'] )
                        {
                            $courseService->uploadImageIfNecessary( $eduOpenCourse['course_image_url'], $course);
                        }
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
                    $this->dbHelper->outputChangedFields($changedFields);
                    if ($this->doModify())
                    {
                        $em->persist($dbCourse);
                        $em->flush();

                        if( $eduOpenCourse['course_image_url'] )
                        {
                            $courseService->uploadImageIfNecessary( $eduOpenCourse['course_image_url'], $dbCourse);
                        }
                    }
                    $courseChanged = true;
                }
            }


            /***************************
             * CREATE OR UPDATE OFFERING
             ***************************/
            $offering = $this->getOfferingEntity( $eduOpenCourse, $course);
            $dbOffering = $this->dbHelper->getOfferingByShortName( $offering->getShortName() );

            if (!$dbOffering)
            {
                if($this->doCreate())
                {
                    $this->out("NEW OFFERING - " . $offering->getName());
                    if ($this->doModify())
                    {
                        $em->persist($offering);
                        $em->flush();
                        $this->dbHelper->sendNewOfferingToSlack( $offering);
                    }


                    $offerings[] = $offering;
                    $courseChanged = true;
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
                    if ($offering->$getter() != $dbOffering->$getter())
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
                    $this->dbHelper->outputChangedFields($changedFields);
                    if ($this->doModify())
                    {
                        $em->persist($dbOffering);
                        $em->flush();
                    }
                    $offerings[] = $dbOffering;
                    $courseChanged = true;
                }
            }
        }

    }

    private function getCourseEntity($c = array())
    {
        $stream = $this->dbHelper->getStreamBySlug('cs');
        $langMap = $this->dbHelper->getLanguageMap();
        $language = $langMap[ 'Italian' ];
        $name = $c['course_name_it'];
        if($c['language'] == 'English' )
        {
            $name = $c['course_name_en'];
            $language = $langMap[ 'English' ];
        }

        if(isset($this->subjectsMap[$c['encategory']]))
        {
            $stream = $this->dbHelper->getStreamBySlug( $this->subjectsMap[$c['encategory']] );
        }

        $course = new Course();
        $course->setShortName( 'eduopen_' . $c['uuid'] );
        $course->setInitiative( $this->initiative );
        $course->setName( $name );
        $course->setDescription( $c['description_local']  );
        $course->setLongDescription( $c['description_local'] );
        $course->setLanguage( $language);
        $course->setStream( $stream ); // Default to Computer Science
        $course->setUrl( self::COURSE_PAGE_URL . $c['uuid'] );
        $course->setCertificate( $c['has_attendance_certificates'] );

        $course->setWorkloadType(Course::WORKLOAD_TYPE_HOURS_PER_WEEK);
        $course->setWorkloadMin( $c['hours_per_weeks'] ) ;
        $course->setWorkloadMax( $c['hours_per_weeks'] ) ;
        // Get the length
        $course->setDurationMax($c['duration_in_weeks']);

        $ins = $this->dbHelper->getInstitutionByName( trim($c['organisation_name_en']) );
        if($ins)
        {
            $course->addInstitution( $ins );
        }
        elseif ($this->dbHelper->getInstitutionByName($c['organisation_name_it']) )
        {
            $course->addInstitution( $this->dbHelper->getInstitutionByName($c['organisation_name_it']) );

        }
        else
        {
          $this->out( $c['organisation_name_en'] );
        }


        return $course;
    }

    private function getOfferingEntity($c = array(), Course $course)
    {
        $offering = new Offering();
        $offering->setShortName('eduopen_' . $c['uuid'] . '_' . $c['start_date']);
        $offering->setCourse( $course );
        $offering->setUrl( self::COURSE_PAGE_URL . $c['uuid'] );
        $startDate = \DateTime::createFromFormat('U', $c['start_date']) ;
        $endDate = \DateTime::createFromFormat('U', $c['start_date']) ;
        $numDays = $c['duration_in_weeks']*7;
        $endDate->add(new \DateInterval("P{$numDays}D"));
        $offering->setStartDate( $startDate );
        $offering->setEndDate($endDate);
        if( $c['enstatus'] == 'Self Pacement')
        {
            $offering->setStatus(Offering::COURSE_OPEN);
        }
        else
        {
            $offering->setStatus(Offering::START_DATES_KNOWN);
        }

        return $offering;
    }
}