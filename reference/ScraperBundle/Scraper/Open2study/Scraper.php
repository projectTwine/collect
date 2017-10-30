<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dhawal
 * Date: 3/24/13
 * Time: 12:59 AM
 * To change this template use File | Settings | File Templates.
 */

namespace ClassCentral\ScraperBundle\Scraper\Open2study;
use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Entity\Course;
use Symfony\Component\Validator\Constraints\Date;

class Scraper extends ScraperAbstractInterface
{
    const COURSE_CATALOGUE = 'https://www.open2study.com/courses';
    const BASE_URL = 'https://www.open2study.com/';
    const API_URL = 'https://www.open2study.com/courses.xml';
    public static $SELF_PACED_COURSES = array(899,904,1645);
    public static $NEXT_SESSION_START_DATE = '2016-03-21';

    public function scrape()
    {
        $this->out("Scraping " . $this->initiative->getName());
        $em = $this->getManager();
        $courses = file_get_contents(sprintf(self::API_URL));
        $simpleXml = simplexml_load_string($courses,'SimpleXMLElement', LIBXML_NOCDATA);
        $courses = json_decode(json_encode((array)$simpleXml), TRUE);

        foreach($courses['node'] as $node)
        {
            $name = trim($node['Title']);

            // Get the course
            $course = $this->dbHelper->findCourseByName($name,$this->initiative);
            if($course)
            {
                if ($node['Status'] && $node['Status'] == 'selfpaced')
                {
                    // Do nothing
                    $this->out("'$name' is self paced");
                }
                else
                {
                    // Get Offering
                    $offering = new Offering();
                    $offering->setCourse($course);
                    $offering->setStatus(Offering::START_DATES_KNOWN);
                    $offering->setStartDate( \DateTime::createFromFormat('U', $node['startdateunix']) );
                    $offering->setEndDate( \DateTime::createFromFormat('U', $node['enddateunix']) );
                    $offering->setShortName( 'open2study_' . $course->getId() . '_' . $node['startdate'] );
                    $offering->setUrl( 'https://www.open2study.com' . $node['Path'] );

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
                        }
                    }
                }

            }
            else
            {
                $this->out("'$name' not found");
            }
        }

        return;
    }

    /**
     * Gets the short name which is a unique key to identify the course
     * @param $courseDetail
     */
    private function getOfferingShortName($courseId, $startDate)
    {
        return strtolower($this->initiative->getCode() . '_' . $courseId. '_'.str_replace('-','_', $startDate));
    }

}