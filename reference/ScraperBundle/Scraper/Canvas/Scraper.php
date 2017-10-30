<?php

namespace ClassCentral\ScraperBundle\Scraper\Canvas;

use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Services\Kuber;

class Scraper extends ScraperAbstractInterface
{

    const COURSE_CATALOG_URL = 'https://www.canvas.net/products.json?page=%s';

    private $courseFields = array(
        'Url', 'Description', 'Name', 'ShortName'
    );

    private $offeringFields = array(
        'StartDate', 'EndDate', 'Url', 'Status', 'ShortName'
    );

    public function scrape()
    {


        $em = $this->getManager();
        $courseService = $this->container->get('course');
        $offerings = array();

        $page = 1;
        while(true)
        {
            $coursesUrl = sprintf(self::COURSE_CATALOG_URL,$page);
            $courses = json_decode(file_get_contents($coursesUrl),true);
            if(empty($courses['products']))
            {
                // No more new courses
                break;
            }

            foreach($courses['products'] as $canvasCourse)
            {
                //$this->output->writeLn( $canvasCourse['title'] );
                if( !$canvasCourse['free'] )
                {
                    // Skip paid courses.
                    continue;
                }


                /****
                 * UPDATE/ADD COURSE
                 */
                $c = $this->getCourse( $canvasCourse );
                $dbCourse = null;
                $dbCourseFromSlug = $this->dbHelper->getCourseByShortName( $c->getShortName() );
                if( $dbCourseFromSlug  )
                {
                    $dbCourse = $dbCourseFromSlug;
                }
                else
                {
                    $dbCourseFromName = $this->dbHelper->findCourseByName($c->getName(), $this->initiative );
                    if($dbCourseFromName)
                    {
                        $dbCourse = $dbCourseFromName;
                    }
                }

                if( empty($dbCourse) )
                {
                    // New Course
                    $this->out("NEW COURSE - " . $c->getName());
                    // Create the course
                    if($this->doCreate())
                    {
                        // NEW COURSE
                        if ($this->doModify())
                        {
                            $em->persist($c);
                            $em->flush();

                            if( $canvasCourse['image'] )
                            {
                                $courseService->uploadImageIfNecessary( $canvasCourse['image'], $c);
                            }

                            // Send an update to Slack
                            $this->dbHelper->sendNewCourseToSlack( $c, $this->initiative );
                        }
                        $courseChanged = true;
                    }
                }
                else
                {
                    $changedFields = $this->dbHelper->changedFields($this->courseFields, $c,$dbCourse);
                    if( !empty($changedFields) && $this->doUpdate() )
                    {
                        $this->out("UPDATE COURSE - " . $dbCourse->getName() . " - ". $dbCourse->getId());
                        $this->dbHelper->outputChangedFields($changedFields);
                        if ($this->doModify())
                        {
                            $em->persist($dbCourse);
                            $em->flush();

                            if( $canvasCourse['image'] )
                            {
                                $courseService->uploadImageIfNecessary( $canvasCourse['image'], $dbCourse);
                            }
                        }
                        $courseChanged = true;
                    }
                    $c = $dbCourse;
                }


                /***************************
                 * CREATE OR UPDATE OFFERING
                 ***************************/
                $offering = $this->getOfferingEntity( $canvasCourse, $c);
                $dbOffering = $this->dbHelper->getOfferingByShortName( $offering->getShortName() );
                if(!$dbOffering) {
                    // find it via url
                    $dbOffering = $this->dbHelper->getOfferingByShortName( $offering->getUrl() );
                }



                if( !$dbOffering )
                {
                    foreach ($c->getOfferings() as $o)
                    {
                        // Check if the course is self paced
                        if($offering->getStatus() == Offering::COURSE_OPEN and $o->getStatus() == Offering::COURSE_OPEN)
                        {
                            $dbOffering = $o;
                            break;
                        }


                        if($o->getStatus() != Offering::COURSE_NA && $o->getStartDate() == $offering->getStartDate())
                        {
                            $dbOffering = $o;
                            break;
                        }
                    }
                }

                if (!$dbOffering) {
                    $this->out("NEW OFFERING - " . $offering->getName() . ' - ' . $offering->getDisplayDate());
                    if ($this->doCreate()) {

                        if ($this->doModify()) {
                            $em->persist($offering);
                            $em->flush();
                        }

                        $this->dbHelper->sendNewOfferingToSlack($offering);
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
                            if($field == 'StartDate' && $offering->getStatus() == Offering::COURSE_OPEN && $dbOffering->getStatus() == Offering::COURSE_OPEN)
                            {
                                // skip update start dates for self paced courses
                                continue;
                            }

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

            $page++;
        }

        return $offerings;

    }

    public function getCourse($canvasCourse)
    {
        $dbLanguageMap = $this->dbHelper->getLanguageMap();

        $course = new Course();
        $course->setName( $canvasCourse['title'] );
        $course->setInitiative($this->initiative);
        $course->setDescription( $canvasCourse['teaser'] );
        $course->setUrl( $canvasCourse['url'] );
        $course->setLanguage( $dbLanguageMap['English']);
        $course->setStream(  $this->dbHelper->getStreamBySlug('cs') ); // Default to Computer Science
        $course->setShortName( 'canvas_' . $this->getSlug( $canvasCourse['path']) );

        return $course;
    }

    /**
     * Remove the session number from the path and returns the session slug.
     * i.e discover-your-value-10 will turn into discover-your-value
     * @param $path
     */
    private function getSlug( $path )
    {
        $sessionNumber = substr(strrchr($path,'-'),1);
        if ( !empty($sessionNumber) && is_numeric($sessionNumber) )
        {
            // slice the session number from the path
            return substr($path,0, strrpos($path,'-'));
        }

        return $path;
    }
    
    private function getOfferingEntity($canvasCourse, Course $course)
    {
        $offering = new Offering();
        $offering->setCourse( $course );
        $offering->setUrl(( $course->getUrl() ));
        $offering->setShortName( 'canvas_' . $canvasCourse['id'] );
        if( $canvasCourse['date'] == 'Self-paced')
        {
            $startDate = new \DateTime();
            $endDate = new \DateTime();
            $endDate->add( new \DateInterval('P30D') );
            $offering->setStatus( Offering::COURSE_OPEN);
            $offering->setStartDate( $startDate );
            $offering->setEndDate( $endDate );
        }
        elseif ( strpos($canvasCourse['date'], 'Ends') !== false )
        {
            // Date contains Ends ...
            $date = substr( $canvasCourse['date'], strpos($canvasCourse['date'], ' '));
            $this->out( $date );
            $startDate = new \DateTime( $date );
            $startDate->sub( new \DateInterval('P30D') );
            $endDate = new \DateTime( $date);

            $offering->setStatus( Offering::COURSE_OPEN);
            $offering->setStartDate( $startDate );
            $offering->setEndDate( $endDate );
            $this->out( $offering->getDisplayDate() );
        }
        elseif ( strpos($canvasCourse['date'], 'Start') === false )
        {
            // Date is of the follow format: Jan 25 - Feb 29, 2016
            $date = explode(',',$canvasCourse['date']);
            $year = $date[1];
            $day = explode('-',$date[0]);
            $startDate = new \DateTime( $day[0] . $year);
            $endDate = new \DateTime( $day[1]. ' ' . $year);
            $offering->setStatus( Offering::START_DATES_KNOWN);
            $offering->setStartDate( $startDate );
            $offering->setEndDate( $endDate );
        }
        else
        {
            // Date contains Started ...
            $date = substr( $canvasCourse['date'], strpos($canvasCourse['date'], ' '));
            $startDate = new \DateTime( $date );
            $endDate = new \DateTime( '2018-12-31');

            $offering->setStatus( Offering::COURSE_OPEN);
            $offering->setStartDate( $startDate );
            $offering->setEndDate( $endDate );
            $this->out( $offering->getDisplayDate() );
        }
        return $offering;
    }
}