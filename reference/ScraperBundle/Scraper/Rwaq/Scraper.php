<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/1/15
 * Time: 5:39 PM
 */

namespace ClassCentral\ScraperBundle\Scraper\Rwaq;


use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;


/**
 * Rwaq has a csv file. It assumed to be at extras/rwaq.csv
 * Class Scraper
 * @package ClassCentral\ScraperBundle\Scraper\Rwaq
 */
class Scraper extends ScraperAbstractInterface {

    const RWAQ_CSV_LOCATION =  'extras/rwaq.csv';

    public function scrape()
    {
        $offerings = array();
        $em = $this->getManager();

        $handle = fopen(self::RWAQ_CSV_LOCATION, 'r');
        fgetcsv($handle); // Ignore the title line
        while (($data = fgetcsv($handle)) !== FALSE)
        {
            // Step 1: Check if the course exists
            $rwaqCourse = $this->getCourseEntity( $data );

            // Check if course exists
            $dbCourse = $this->dbHelper->getCourseByShortName( $rwaqCourse->getShortName() );
            if( !$dbCourse )
            {
                $this->out( 'COURSE NOT FOUND - ' . $rwaqCourse->getName() . ' - ' . $rwaqCourse->getShortName() );
                if( $this->doModify() )
                {
                    // Create this course.
                    $em->persist ($rwaqCourse);
                    $em->flush();

                    $this->out( 'COURSE CREATED - ' . $rwaqCourse->getName() . ' - ' . $rwaqCourse->getShortName() );
                }
            }
            else
            {
                $rwaqCourse = $dbCourse;
            }

            // STEP 2: Check if the offering exists
            $rwaqOffering = $this->getOfferingEntity( $data);
            $rwaqOffering->setCourse( $rwaqCourse );

            $dbOffering = $this->dbHelper->getOfferingByShortName( $rwaqOffering->getShortName() );

            if( !$dbOffering )
            {
                $this->out( 'OFFERING NOT FOUND - ' . $rwaqCourse->getName() . ' - ' . $rwaqOffering->getShortName() );
                if( $this->doModify() )
                {
                    // Create this course.
                    $em->persist ($rwaqOffering);
                    $em->flush();

                    $this->out( 'OFFERING CREATED - ' . $rwaqCourse->getName() . ' - ' . $rwaqOffering->getShortName() );
                    $offerings[] = $rwaqOffering;
                }
            }
            else
            {
                $rwaqOffering = $dbOffering;
            }

        }

        return $offerings;
    }

    /**
     * Build a doctrine Course Entity out of a csv row
     * @param $row
     * @return Course
     */
    public function getCourseEntity( $row )
    {
        $course = new Course();
        $course->setName( $row[0] );
        $course->setDescription( $row[1] );
        $course->setVideoIntro( str_replace('http','https',$row[4]) );
        $course->setUrl( $row[5] );
        $course->setShortName( $this->getCourseId( $row[5] ) );
        $course->setInitiative( $this->initiative );
        // Set the language to arabic
        $langMap = $this->dbHelper->getLanguageMap();
        $course->setLanguage( $langMap['Arabic']);

        // Set the default stream as humanities
        $defaultStream = $this->dbHelper->getStreamBySlug('humanities');
        $course->setStream( $defaultStream );

        // Calculate the length of the course
        $start = new \DateTime( $row[2] );
        $end = new \DateTime( $row[3]);
        $length = ceil( $start->diff($end)->days/7 );
        $course->setLength( $length );

        return $course;
    }

    /**
     * Build a doctrine Offering Entity out of a csv row
     * @param $row
     * @return Offering
     */
    public function getOfferingEntity( $row )
    {
        $offering = new Offering();
        $offering->setShortName( $this->getOfferingId( $row[5] ));
        $offering->setStartDate( new \DateTime( $row[2] ) );
        $offering->setEndDate( new \DateTime( $row[3]) );
        $offering->setStatus( Offering::START_DATES_KNOWN );
        $offering->setUrl( $row[5] );

        return $offering;
    }


    /**
     * Parses the url to create a unique id for the course
     * i.e http://www.rwaq.org/courses/introduction-to-dentistry-2
     * course id will be rwaq-introduction-to-dentistry
     * @param $url
     */
    public function getCourseId( $url )
    {
        $offeringId = $this->getOfferingId( $url );
        $courseId = null;
        // Check if the offering id ends with a number. i.e -2,-3. If it does remove it and return the rest of the code
        $last = substr($offeringId, strrpos($offeringId,'-')+1);
        if( is_numeric( $last) )
        {
            $courseId =  substr($offeringId, 0, strrpos($offeringId,'-'));
        }
        else
        {
            $courseId =  $offeringId;
        }

        // trim to length of 50
        return substr( $courseId,0,50);
    }

    /**
     * Parses the url to get unique id for the offering
     * i.e http://www.rwaq.org/courses/introduction-to-dentistry-2
     * offering id will be rwaq-introduction-to-dentistry-2
     * @param $url
     */
    public function getOfferingId( $url )
    {
        return 'rwaq-' . substr($url, strrpos($url,'/')+1);
    }


} 