<?php

namespace ClassCentral\ScraperBundle\Scraper\Kadenze;

use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;

class Scraper extends \ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface
{

    const COURSES_API_ENDPOINT = 'https://www.kadenze.com/catalog.json?source=classcentral';

    private $courseFields = array(
        'Url', 'Description','Name', 'ShortName','Description','LongDescription','Certificate','CertificatePrice'
    );

    private $offeringFields = array(
        'StartDate', 'EndDate', 'Url', 'Status'
    );

    private $subjectMap = array(
        'Music' => 'music',
        'Creative Computing' => 'cs',
        'History and Culture' => 'language-culture',
        'Visual Arts' => 'visual-arts',
        'Business' => 'business',
        'Fashion' => 'art-and-design',
        'Entertainment Technology' =>'art-and-design',
        'Web Development' => 'web-development',
        'Design' => 'design-and-creativity',
        'Photography' => 'art-and-design',
    );

    public function scrape()
    {
        $em = $this->getManager();
        $kCourses = json_decode(file_get_contents( self::COURSES_API_ENDPOINT ), true );
        $coursesChanged = array();
        $courseService = $this->container->get('course');

        foreach($kCourses as $kcourse)
        {
            $course =  $this->getCourseEntity( $kcourse );
            $dbCourse = $this->dbHelper->getCourseByShortName( $course->getShortName() );

            if(!$dbCourse)
            {
                $dbCourse = $this->dbHelper->findCourseByName( $course->getName(), $this->initiative);
            }

            if(empty($dbCourse))
            {
                if($this->doCreate())
                {
                    $this->out("NEW COURSE - " . $course->getName());

                    // NEW COURSE
                    if ($this->doModify())
                    {
                        // Add instructors
                        foreach( $kcourse['instructors'] as $staff )
                        {
                            $insName = $staff['full_name'];
                            if(!empty($insName))
                            {
                                $course->addInstructor($this->dbHelper->createInstructorIfNotExists($insName));
                            }
                        }

                        $em->persist($course);
                        $em->flush();

                        $this->dbHelper->sendNewCourseToSlack( $course, $this->initiative );

                        if( $kcourse['logo'] )
                        {
                            $courseService->uploadImageIfNecessary( $kcourse['logo'], $course);
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

                    // Course has been modified
                    $this->out("UPDATE COURSE - " . $dbCourse->getName() . " - ". $dbCourse->getId());
                    $this->dbHelper->outputChangedFields($changedFields);
                    if ($this->doModify())
                    {
                        $em->persist($dbCourse);
                        $em->flush();

                        if( $kcourse['logo'] )
                        {
                            $courseService->uploadImageIfNecessary( $kcourse['logo'], $dbCourse);
                        }
                    }
                    $courseChanged = true;
                }

                $course = $dbCourse;
            }

            /***************************
             * CREATE OR UPDATE OFFERING
             ***************************/

            foreach($kcourse['offerings'] as $o)
            {
                $offering = $this->getOffering($o,$course);
                $dbOffering = $this->dbHelper->getOfferingByShortName($offering->getShortName());
                if (!$dbOffering) {
                    if ($this->doCreate()) {
                        $this->out("NEW OFFERING - " . $offering->getName());
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
    }

    public function getCourseEntity($course)
    {
        $stream = $this->dbHelper->getStreamBySlug('art-and-design');
        $langMap = $this->dbHelper->getLanguageMap();
        $defaultLanguage = $langMap[ 'English' ];

        foreach($course['categories'] as $category)
        {
            if(isset($this->subjectsMap[ $category ]))
            {
                $stream = $this->dbHelper->getStreamBySlug( $this->subjectsMap[ $category ] );
            }
            break;
        }

        $c = new \ClassCentral\SiteBundle\Entity\Course();
        $c->setName($course['name']);
        $c->setUrl($course['url']);
        $c->setInitiative( $this->initiative );
        $c->setShortName( 'kadenze_'. $course['id'] );
        $c->setVideoIntro( $course['promo_video']);
        $c->setLanguage( $defaultLanguage);
        $c->setStream($stream); // Default to Art and Design

        $c->setDescription( $course['description'] );
        $c->setLongDescription( $course['description'] );
        $c->setCertificate(true);
        $c->setCertificatePrice(Course::PAID_CERTIFICATE);

        $ins = $this->dbHelper->getInstitutionByName( trim($course['organization']) );
        if($ins)
        {
            $c->addInstitution($ins);
        }

        return $c;
    }

    public function getOffering($o, Course $course)
    {
        $offering = new Offering();
        $offering->setShortName( 'kadenze_' . $o['id'] );
        $offering->setCourse( $course );
        $offering->setUrl( $course->getUrl() );
        $offering->setStatus( Offering::START_DATES_KNOWN );
        $dateString = false;
        $adaptive = $o['adaptive'];

        try
        {
            $startDate = new \DateTime($o['start_date']);
            $endDate = null;
            if( !empty($o['end_date']) )
            {
                $endDate = new \DateTime( $o['end_date'] );
            }
            else
            {
                $now = new \DateTime();
                if($now > $startDate)
                {
                    // The course has already started
                    $now->add( new \DateInterval('P30D'));
                    $endDate = $now;
                }
                else
                {
                    // The course hasn't started. End date one month after start date
                    $endDate = new \DateTime( $o['start_date'] );
                    $endDate->add(new \DateInterval('P30D'));
                }
            }
            $offering->setStartDate($startDate);
            $offering->setEndDate($endDate);

            // If adaptive is true and now is between startDate and endDate set the status to self paced
            $today = new \DateTime();
            if($adaptive && $today >= $startDate && $today <= $endDate)
            {
                $offering->setStatus(Offering::COURSE_OPEN);
            }

        } catch (\Exception $e)
        {
            $dateString = true;
        }

        // Start date is text like Fall 2017 or just 2017
        if($dateString || is_numeric($o['start_date']))
        {
            // The date is a string. Probably means the course is sometime in the future
            $offering->setStatus( Offering::START_YEAR_KNOWN );

            $startDate = new \DateTime();
            $endDate = new \DateTime();

            // Arbitary start dates two months later
            $startDate->add(new \DateInterval('P60D'));
            $endDate->add(new \DateInterval('P90D'));

            $offering->setStartDate($startDate);
            $offering->setEndDate($endDate);
        }

        return $offering;
    }
}