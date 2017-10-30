<?php

namespace ClassCentral\ScraperBundle\Scraper\Federica;

use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;

class Scraper extends ScraperAbstractInterface
{

    const COURSES_FEED = 'http://www.federica.eu/feed-courses.php';

    private $courseFields = array(
        'Url', 'Description', 'Name','LongDescription','Language'
    );

    private $offeringFields = array(
        'StartDate', 'EndDate', 'Url', 'Status'
    );

    public function scrape()
    {
        $em = $this->getManager();
        $courseService = $this->container->get('course');

        $fCourses = file_get_contents(sprintf(self::COURSES_FEED));
        $fCourses = str_replace("feu:","feu-",$fCourses);
        $simpleXml = simplexml_load_string($fCourses,'SimpleXMLElement', LIBXML_NOCDATA);
        $fCourses = json_decode(json_encode((array)$simpleXml), TRUE);

        $coursesChanged = array();
        foreach($fCourses['channel']['item'] as $fCourse)
        {

            $courseChanged = false;
            $course =  $this->getCourseEntity( $fCourse );
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
                        $insName = $fCourse['feu-author'];
                        if(!empty($insName))
                        {
                            $course->addInstructor($this->dbHelper->createInstructorIfNotExists($insName));
                        }

                        $em->persist($course);
                        $em->flush();

                        $this->dbHelper->sendNewCourseToSlack( $course, $this->initiative );

                        if( $fCourse['feu-cover'] )
                        {
                            $courseService->uploadImageIfNecessary( $fCourse['feu-cover'], $course);
                        }

                        // Send an update to Slack

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

                        if( $fCourse['feu-cover'] )
                        {
                            $courseService->uploadImageIfNecessary($fCourse['feu-cover'], $dbCourse);
                        }
                    }
                    $courseChanged = true;
                }

                $course = $dbCourse;
            }

            /***************************
             * CREATE OR UPDATE OFFERING
             ***************************/
            $offering = $this->getOfferingEntity( $fCourse, $course);
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
                    }

                    $this->dbHelper->sendNewOfferingToSlack( $offering);
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

            if( $courseChanged )
            {
                $coursesChanged[] = $course;
            }
        }
    }

    private function getCourseEntity ($c = array())
    {
        $defaultStream = $this->dbHelper->getStreamBySlug('cs');
        $langMap = $this->dbHelper->getLanguageMap();
        $defaultLanguage = $langMap[ 'Italian' ];
        if( !empty($c['feu-language']) && $c['feu-language']=='en' )
        {
            $defaultLanguage = $langMap[ 'English' ];
        }

        $course = new Course();
        $course->setShortName( 'federica_'. $c['feu-unique_id'] );
        $course->setInitiative( $this->initiative );
        $course->setName( $c['title'] );
        $course->setDescription( $c['description'] );
        $course->setLongDescription( $c['description'] );
        $course->setLanguage( $defaultLanguage );
        $course->setStream($defaultStream); // Default to Computer Science
        $course->setUrl($c['link']);

        $course->addInstitution($this->dbHelper->getInstitutionBySlug('university-of-naples'));

        return $course;
    }


    private function getOfferingEntity ($fCourse, $course)
    {
        $offering = new Offering();
        $offering->setShortName( 'federica_'. $fCourse['feu-unique_id'].'_'. $fCourse['feu-start_date']);
        $offering->setCourse( $course );
        $offering->setUrl( $course->getUrl() );

        $startDate = new \DateTime($fCourse['feu-start_date']);
        $endDate = new \DateTime($fCourse['feu-end_date']);
        $offering->setStartDate( $startDate );
        $offering->setEndDate( $endDate );
        $offering->setStatus( Offering::START_DATES_KNOWN );

        $dt = new \DateTime('2016-01-01');
        if($startDate < $dt)
        {
            // Courses with no start date
            $offering->setStartDate( new \DateTime('2019-01-01') );
            $offering->setEndDate(  new \DateTime('2019-01-06') );
            $offering->setStatus( Offering::START_DATES_UNKNOWN );
        }

        return $offering;
    }

}