<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 2/13/14
 * Time: 4:58 PM
 */

namespace ClassCentral\ScraperBundle\Scraper\Iversity;


use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Offering;

class Scraper extends ScraperAbstractInterface {

    const COURSES_API_ENDPOINT = 'http://www.kimonolabs.com/api/5kbi8ily';
    const COURSE_API_ENDPOINT = 'http://www.kimonolabs.com/api/3spuu7t8';

    public function scrape()
    {
        $result = json_decode( $this->getCoursesJson(), true );

        foreach($result['results']['collection1'] as $course)
        {
            $courseName = $course['name']['text'];
            $url = $course['name']['href'];

            $slug = substr($url,strrpos($url,'/')+1);

            $courseInfo = json_decode($this->getCourseInfo($slug), true);
            $startDate = $courseInfo['results']['collection1'][0]['start_date'];

            $osn = 'iversity-'. $slug;

            $offering = $this->dbHelper->getOfferingByShortName($osn);
            if(!$offering)
            {
                $this->out("NOT FOUND");
                $this->out("$courseName - $startDate");
                $this->out($url);
                $this->out("");
                continue;
            }

            try {
                if($offering->getStatus() == Offering::START_DATES_KNOWN)
                {
                    $offeringStartDate = new \DateTime($startDate);
                    if($offeringStartDate != $offering->getStartDate() )
                    {
                        $this->out("INCORRECT START DATE");
                        $this->out("$courseName - $startDate - Offering Id : {$offering->getId()}");
                        $this->out("Offering Date - {$offering->getDisplayDate()}");
                        $this->out($url);
                        $this->out("");
                    }

                }

                if($offering->getStatus() == Offering::START_MONTH_KNOWN && trim($startDate) != $offering->getStartDate()->format("F Y"))
                {
                    $this->out("INCORRECT START MONTH");
                    $this->out("$courseName - $startDate - Offering Id : {$offering->getId()}");
                    $this->out("Offering Date - {$offering->getDisplayDate()}");
                    $this->out($url);
                    $this->out("");
                }

            } catch(\Exception $e) {
                $this->out("Error parsing dates");
                $this->out("$courseName - $startDate - Offering Id : {$offering->getId()}");
                $this->out("Offering Date - {$offering->getDisplayDate()}");
                $this->out($url);
                $this->out("");

            }
        }

        return array();
    }

    /**
     * Returns a json for all the iversity courses
     */
    private function getCoursesJson()
    {
        $apiKey = $this->container->getParameter('kimono_api_key');
        $url = self::COURSES_API_ENDPOINT . '?apikey=' . $apiKey;

        return file_get_contents($url);
    }

    private function getCourseInfo($slug)
    {
        $apiKey = $this->container->getParameter('kimono_api_key');
        $url = self::COURSE_API_ENDPOINT . '?apikey=' . $apiKey . '&kimpath2='.$slug;

        return file_get_contents($url);
    }

} 