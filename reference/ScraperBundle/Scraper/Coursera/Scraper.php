<?php

namespace ClassCentral\ScraperBundle\Scraper\Coursera;

use ClassCentral\CredentialBundle\Entity\Credential;
use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\CourseStatus;
use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Institution;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Services\Kuber;
use ClassCentral\SiteBundle\Utility\PageHeader\PageHeaderFactory;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class Scraper extends ScraperAbstractInterface {

    const COURSES_JSON = 'https://www.coursera.org/maestro/api/topic/list?full=1';
    const INSTRUCTOR_URL = 'https://www.coursera.org/maestro/api/user/instructorprofile?topic_short_name=%s&exclude_topics=1';
    const BASE_URL = 'https://www.coursera.org/course/';
    const COURSE_CATALOG_URL = 'https://api.coursera.org/api/catalog.v1/courses?id=%d&fields=language,aboutTheCourse,courseSyllabus,estimatedClassWorkload&includes=sessions';
    const SESSION_CATALOG_URL = 'https://api.coursera.org/api/catalog.v1/sessions?id=%d&fields=eligibleForCertificates,eligibleForSignatureTrack';
    const ONDEMAND_COURSE_URL = 'https://www.coursera.org/api/onDemandCourses.v1?fields=partners.v1(squareLogo,rectangularLogo),instructors.v1(fullName),overridePartnerLogos&includes=instructorIds,partnerIds,_links&&q=slug&slug=%s';

    // Contains courses schedule
    const ONDEMAND_OPENCOURSE_API = 'https://www.coursera.org/api/opencourse.v1/course/%s?showLockedItems=true';
    CONST ONDEMAND_COURSE_SCHEDULE = 'https://www.coursera.org/api/onDemandCourseSchedules.v1/%s/?fields=defaultSchedule';
    const ONDEMAND_COURSE_MATERIALS = 'https://www.coursera.org/api/onDemandCourseMaterials.v1/?q=slug&slug=%s&includes=moduleIds,lessonIds,itemIds,tracks&fields=moduleIds,onDemandCourseMaterialModules.v1(name,slug,description,timeCommitment,lessonIds,optional),onDemandCourseMaterialLessons.v1(name,slug,timeCommitment,itemIds,optional,trackId),onDemandCourseMaterialItems.v1(name,slug,timeCommitment,content,isLocked,lockableByItem,itemLockedReasonCode,trackId),onDemandCourseMaterialTracks.v1(passablesCount)&showLockedItems=true';

    const ONDEMAND_SESSION_IDS = 'https://www.coursera.org/api/onDemandSessions.v1/?q=currentOpenByCourse&courseId=%s&includes=memberships&fields=moduleDeadlines';

    const COURSE_CATALOG_URL_v2 = 'https://www.coursera.org/api/catalogResults.v2?q=search&query=&limit=5000&debug=false&fields=debug,courseId,domainId,onDemandSpecializationId,specializationId,subdomainId,suggestions,courses.v1(name,description,slug,photoUrl,courseStatus,partnerIds),onDemandSpecializations.v1(name,description,slug,logo,courseIds,launchedAt,partnerIds),specializations.v1(name,description,shortName,logo,primaryCourseIds,display,partnerIds),partners.v1(name)&includes=courseId,domainId,onDemandSpecializationId,specializationId,subdomainId,suggestions,courses.v1(partnerIds),onDemandSpecializations.v1(partnerIds),specializations.v1(partnerIds)';
    const COURSE_FACILITATED_GROUPS = 'https://www.coursera.org/api/onDemandFacilitatedGroups.v1/?q=firstAvailableInScope&scopeId=session~%s!~%s&fields=groupId,scopeId,facilitators,mentorProfiles.v1(fullName,bio,email,photoUrl,title,social),onDemandFacilitatedGroupAvailabilities.v1(spotsTaken,spotsLeft,memberLimit,hasAvailability)&includes=mentorProfiles,availability';

    // CREDENTIAL_URS
    const SPECIALIZATION_CATALOG_URL = 'https://www.coursera.org/api/specializations.v1';
    const SPECIALIZATION_URL  = 'https://www.coursera.org/maestro/api/specialization/info/%s?currency=USD&origin=US';
    const SPECIALIZATION_PAGE_URL = 'https://www.coursera.org/specialization/%s/%s?utm_medium=classcentral';

    const SPECIALIZATION_ONDEMAND_CATALOG_URL = 'https://www.coursera.org/api/onDemandSpecializations.v1';
    const SPECIALIZATION_ONDEMAND_URL = 'https://www.coursera.org/api/onDemandSpecializations.v1?fields=capstone,courseIds,description,instructorIds,interchangeableCourseIds,logo,metadata,partnerIds,partnerLogoOverrides,tagline,partners.v1(description,name,squareLogo),instructors.v1(firstName,lastName,middleName,partnerIds,photo,prefixName,profileId,shortName,suffixName,title),courses.v1(courseProgress,courseType,description,instructorIds,membershipIds,name,startDate,subtitleLanguages,v1Details,vcMembershipIds,workload),v1Details.v1(courseSyllabus),memberships.v1(grade,vcMembershipId),vcMemberships.v1(certificateCodeWithGrade)&includes=courseIds,instructorIds,partnerIds,instructors.v1(partnerIds),courses.v1(courseProgress,instructorIds,membershipIds,subtitleLanguages,v1Details,vcMembershipIds)&q=slug&slug=%s';
    const SPECIALIZATION_ONDEMAND_PAGE_URL = 'https://www.coursera.org/specializations/%s?utm_medium=classcentral';

    const PRODUCT_PRICES = 'https://www.coursera.org/api/productPrices.v3/VerifiedCertificate~%s~USD~US';
    protected static $languageMap = array(
        'en' => "English",
        'en,pt' => "English",
        'fr' => "French",
        "de" => "German",
        "es" => "Spanish",
        "it" => "Italian",
        "zh-Hant" => "Chinese",
        "zh-Hans" => "Chinese",
        "zh-cn" => "Chinese",
        "zh-tw" => "Chinese",
        "zh-TW" => "Chinese",
        "zh-CN" => "Chinese",
        "zh"    => "Chinese",
        "ar" => "Arabic",
        "ru" => "Russian",
        "tr" => "Turkish",
        "he" => "Hebrew",
        'pt-br' => 'Portuguese',
        'pt-BR' => 'Portuguese',
        'pt' => 'Portuguese',
        'pt-PT' => 'Portuguese',
    );

    private $courseFields = array(
        'Url', 'SearchDesc', 'Description', 'Name', 'Language','LongDescription','Syllabus', 'WorkloadMin', 'WorkloadMax','WorkloadType',
        'Certificate','CertificatePrice' ,'VideoIntro','DurationMin','DurationMax'
    );

    private $onDemandCourseFields = array(
        'Url', 'Description', 'Name', 'Language','LongDescription','Syllabus',
        'Certificate','CertificatePrice','DurationMin','DurationMax'
    );

    private $credentialFields = array(
        'Url','Description','Name', 'OneLiner', 'SubTitle'
    );


    private $offeringFields = array(
        'StartDate', 'EndDate', 'Status','Url','ShortName'
    );

    public static $credentialSlugs = array(
        'computer-fundamentals' => 'fundamentalscomputing2',
        'data-mining' => 'datamining',
        'cyber-security' => 'cybersecurity',
        'virtual-teacher' => 'virtualteacher',
        'computational-biology' => 'bioinformatics',
        'content-strategy' => 'contentstrategy'
    );

    public function scrape()
    {
        if($this->isCredential)
        {
            $this->scrapeCredentials();
            return;
        }
        $em = $this->getManager();
        $courseService = $this->container->get('course');
        $offerings = array();

        $allCourses = $this->buildCourseraCoursesJson();
        foreach ($allCourses as $onDemandCourse)
        {
                $c = $this->getOnDemandCourse( $onDemandCourse );

                $dbCourse = null;
                $dbCourseFromSlug = $this->dbHelper->getCourseByShortName( $c->getShortName() );
                if( $dbCourseFromSlug  )
                {
                    $dbCourse = $dbCourseFromSlug;
                }
                else
                {
                    $dbCourseFromName = $this->findCourseByName($c->getName(), $this->initiative );
                    if($dbCourseFromName && $c->getName() != 'Portfolio and Risk Management')
                    {
                        $dbCourse = $dbCourseFromName;
                    }
                }

                if( empty($dbCourse) )
                {
                    // Create the course
                    if($this->doCreate())
                    {
                        $this->out("NEW COURSE - " . $c->getName());

                        // NEW COURSE
                        if ($this->doModify())
                        {
                            $em->persist($c);
                            $em->flush();


                            if( $onDemandCourse['elements'][0]['promoPhoto'] )
                            {
                                $courseService->uploadImageIfNecessary( $onDemandCourse['elements'][0]['promoPhoto'], $c);
                            }

                            // Send an update to Slack
                            $this->dbHelper->sendNewCourseToSlack( $c, $this->initiative );
                            $dbCourse = $c;
                        }
                    }
                }
                else
                {
                    // Update the course details
                    $changedFields = $this->dbHelper->changedFields($this->onDemandCourseFields,$c,$dbCourse);
                    if(!empty($changedFields) && $this->doUpdate())
                    {
                        $this->out("UPDATE COURSE - " . $dbCourse->getName() );
                        $this->outputChangedFields( $changedFields );
                        if ($this->doModify())
                        {
                            $em->persist($dbCourse);
                            $em->flush();
                        }
                    }

                    if( $this->doUpdate() && $this->doModify())
                    {
                        $courseService->uploadImageIfNecessary($onDemandCourse['elements'][0]['promoPhoto'],$dbCourse);
                    }

                    // Check how many of them are self paced
                    $selfPaced = false;

                    if ( $dbCourse->getNextOffering()->getStatus() == Offering::COURSE_OPEN )
                    {
                        $selfPaced = true;
                    }


                    // Update the sessions.
                    $courseId = $onDemandCourse['elements'][0]['id'];
                    $sessionDetails = $onDemandCourse['sessionDetails'];
                    if(empty($sessionDetails['elements']))
                    {
                        // Create an offering
                        $offering = new Offering();
                        $offering->setShortName( $dbCourse->getShortName() );
                        $offering->setUrl( $dbCourse->getUrl() );
                        $offering->setCourse( $dbCourse );

                        if( isset($onDemandCourse['elements'][0]['plannedLaunchDate']))
                        {
                            try
                            {
                                // Self paced Not Started - But will Start in the future
                                $this->out("SELF PACED FUTURE COURSE : " . $dbCourse->getName() );
                                $startDate = new \DateTime( $onDemandCourse['elements'][0]['plannedLaunchDate'] );
                                $endDate =  new \DateTime(  $onDemandCourse['elements'][0]['plannedLaunchDate']  );
                                $endDate->add( new \DateInterval("P30D") );
                                $offering->setStatus( Offering::START_DATES_KNOWN );
                            }
                            catch(\Exception $e)
                            {
                                continue;
                            }
                        }
                        else
                        {
                            // Self paced course that can be accessed right now
                            $this->out("SELF PACED COURSE : " . $dbCourse->getName() );
                            $startDate = new \DateTime();
                            $offering->setStatus( Offering::COURSE_OPEN );
                            $endDate =  new \DateTime( );
                            $endDate->add( new \DateInterval("P30D") );

                            if($dbCourse->getNextOffering()->getStatus() == Offering::COURSE_OPEN )
                            {
                                // Already self paced nothing to be done here
                                continue;
                            }
                        }

                        $offering->setStartDate( $startDate );
                        $offering->setEndDate( $endDate );

                        // Check if offering exists
                        $dbOffering = $this->dbHelper->getOfferingByShortName( $dbCourse->getShortName() );

                        if($dbOffering)
                        {
                            // Check if the dates and other details are right
                            $this->offeringChangedFields($offering,$dbOffering);
                        }
                        else
                        {
                            // Save and Create the offering
                            if($this->doCreate())
                            {
                                $this->out("NEW OFFERING - " . $offering->getName() );
                                if ($this->doModify())
                                {
                                    $em->persist($offering);
                                    $em->flush();
                                    $this->dbHelper->sendNewOfferingToSlack( $offering);
                                }

                            }
                        }
                    }
                    else
                    {

                        $dbOffering = null;
                        // Regularly Scheduled Course
                        $this->out("Regularly Scheduled Course : " . $dbCourse->getName() );
                        foreach($dbCourse->getOfferings() as $o)
                        {
                           if( $o->getShortName() == $dbCourse->getShortName() )
                           {
                               $dbOffering = $o; // A course with future announced date becomes current and has sessions
                               break;
                           }
                        }
                        foreach( $sessionDetails['elements'] as $session )
                        {
                            $sessionId = $session['id'];
                            $offeringShortName = 'coursera_' . $sessionId;
                            // Create an offering
                            $offering = new Offering();
                            $offering->setShortName( $offeringShortName );
                            $offering->setUrl( $dbCourse->getUrl() );
                            $offering->setCourse( $dbCourse );
                            $offering->setStatus( Offering::START_DATES_KNOWN );

                            $startDate = new \DateTime( '@'. intval($session['startedAt']/1000) );
                            $endDate =new \DateTime( '@'. intval($session['endedAt']/1000) );
                            $startDate->setTimezone( new \DateTimeZone('America/Los_Angeles') );
                            $endDate->setTimezone( new \DateTimeZone('America/Los_Angeles') );

                            $offering->setStartDate( $startDate );
                            $offering->setEndDate( $endDate );

                            // Check if offering exists
                            if(!$dbOffering)
                            {
                                $dbOffering = $this->dbHelper->getOfferingByShortName( $offeringShortName );
                            }
                            if($dbOffering)
                            {
                                // Check if the dates and other details are right
                                $this->offeringChangedFields($offering,$dbOffering);
                            }
                            else
                            {
                                if($this->doCreate())
                                {
                                    $this->out("NEW OFFERING - " . $offering->getName() );
                                    if ($this->doModify())
                                    {
                                        $em->persist($offering);
                                        $em->flush();
                                        $this->dbHelper->sendNewOfferingToSlack( $offering);
                                    }
                                }
                            }
                            $dbOffering = null;
                        }
                    }


                    if( !$selfPaced )
                    {
                        //$this->out("OnDemand Session Missing : " . $element['name']) ;
                    }
                }
        }

        return $offerings;
    }

    private function offeringChangedFields($offering, $dbOffering )
    {
        $offeringModified = false;
        $changedFields = array();
        $em = $this->getManager();
        foreach ($this->offeringFields as $field)
        {
            $getter = 'get' . $field;
            $setter = 'set' . $field;

            $oldValue =  $dbOffering->$getter();
            $newValue = $offering->$getter();

            // Date comparision fails due to different time zones
            if( gettype($oldValue) == 'object' && get_class($oldValue) == 'DateTime')
            {
                $oldValue =  $dbOffering->$getter()->format('jS M, Y');
                $newValue = $offering->$getter()->format('jS M, Y');
            }

            if ( $oldValue  !=  $newValue)
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
        }
    }


    private function getOnDemandCourse( $data = array() )
    {
        $dbLanguageMap = $this->dbHelper->getLanguageMap();

        $course = new Course();
        $course->setShortName( substr('coursera_' . $data['elements'][0]['slug'], 0, 49));
        $course->setInitiative($this->initiative);
        $course->setName( $data['elements'][0]['name'] );
        $course->setDescription( $data['elements'][0]['description'] );
        $course->setLongDescription( nl2br($data['elements'][0]['description']) );
        $course->setStream(  $this->dbHelper->getStreamBySlug('cs') ); // Default to Computer Science
        $course->setUrl( 'https://www.coursera.org/learn/'. $data['elements'][0]['slug']);

        $lang = null;
        if (isset(self::$languageMap[ $data['elements']['0']['primaryLanguageCodes'][0] ]))
        {
            $lang = self::$languageMap[ $data['elements']['0']['primaryLanguageCodes'][0] ];
        }

        if(isset( $dbLanguageMap[$lang] ) ) {
            $course->setLanguage( $dbLanguageMap[$lang] );
        } else {
            $this->out("Language not found " . $data['elements']['0']['primaryLanguageCodes'][0] );
            $course->setLanguage($dbLanguageMap['English']); // Use default language english
        }

        $course->setCertificate( $data['elements'][0]['isVerificationEnabled'] );
        $course->setCertificatePrice(Course::PAID_CERTIFICATE); // Price not known. Signify paid certificate.

        // Add the university
        foreach ($data['linked']['partners.v1'] as $university)
        {
            $ins = new Institution();
            $ins->setName($university['name']);
            $ins->setIsUniversity(true);
            $ins->setSlug($university['shortName']);
            $course->addInstitution($this->dbHelper->createInstitutionIfNotExists($ins));
        }

        foreach ( $data['linked']['instructors.v1'] as $courseraInstructor)
        {
            if(!empty( $courseraInstructor['fullName'] ) )
            {
                $insName = $courseraInstructor['fullName'] ;
            }
            else
            {
                $insName = $courseraInstructor['firstName'] . ' ' . $courseraInstructor['lastName'];
            }

            $course->addInstructor($this->dbHelper->createInstructorIfNotExists($insName));
        }


        // Get Course Details like Syllabus and length
        $courseDetails = $data['courseDetails'];
        if( !empty($courseDetails) )
        {
            $syllabus = '';
            foreach($courseDetails['courseMaterial']['elements'] as $item)
            {
                $syllabus .= "<b>{$item['name']}</b><br/>{$item['description']}<br/><br/>";

            }
            $course->setSyllabus( $syllabus);
        }

        // Calculate the length of the course
        $schedule = $data['schedule'];
        if( !empty($schedule) )
        {
            $length = 0;
            foreach( $schedule['elements'][0]['defaultSchedule']['periods'] as $period)
            {
                $length += $period['numberOfWeeks'];
            }

            if($length > 0)
            {
                // Length of the course in weeks
                $course->setDurationMin($length);
                $course->setDurationMax($length);
            }
        }


        return $course;
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

    private function findCourseByName ($title, Initiative $initiative)
    {
        $em = $this->getManager();
        $result = $em->getRepository('ClassCentralSiteBundle:Course')->createQueryBuilder('c')
            ->where('c.initiative = :initiative' )
            ->andWhere('c.name LIKE :title')
            ->andWhere('c.status = :status')
            ->setParameter('initiative', $initiative)
            ->setParameter('title', $title)
            ->setParameter('status', CourseStatus::AVAILABLE)
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
//        $specializations = json_decode(file_get_contents( self::SPECIALIZATION_CATALOG_URL ),true);
//        foreach($specializations['elements'] as $item)
//        {
//            $details = json_decode(file_get_contents( sprintf(self::SPECIALIZATION_URL, $item['id']) ),true);
//
//            $credential =$this->getCredentialFromSpecialization( $details );
//            $this->saveOrUpdateCredential( $credential, $details['logo'] );
//        }

        // Scrape Ondemand specializations
        $onDemandSpecializations = json_decode($this->file_get_contents_wrapper( self::SPECIALIZATION_ONDEMAND_CATALOG_URL ),true);
        foreach( $onDemandSpecializations['elements'] as $item )
        {
            $details = json_decode($this->file_get_contents_wrapper( sprintf(self::SPECIALIZATION_ONDEMAND_URL, $item['slug']) ),true);
            $credential = $this->getCredentialFromOnDemandSpecialization( $details );
            $this->saveOrUpdateCredential( $credential, $details['elements'][0]['logo'] );
        }
    }

    private function getCredentialFromOnDemandSpecialization($details )
    {
        $credential = new Credential();

        $credential->setName( $details['elements'][0]['name'] );
        $credential->setPricePeriod(Credential::CREDENTIAL_PRICE_PERIOD_TOTAL);
        $credential->setPrice(0);
        if( isset(self::$credentialSlugs[$details['elements'][0]['slug'] ]))
        {
            $details['elements'][0]['slug']  = self::$credentialSlugs[$details['elements'][0]['slug'] ];
        }
        $credential->setSlug( $details['elements'][0]['slug'] . '-specialization' );
        $credential->setInitiative( $this->initiative );
        $credential->setUrl( sprintf(self::SPECIALIZATION_ONDEMAND_PAGE_URL,$details['elements'][0]['slug']));
        $credential->setOneLiner( $details['elements'][0]['metadata']['subheader'] );

        if( isset($details['elements'][0]['metadata']['headline']) )
        {
            $credential->setSubTitle(  $details['elements'][0]['metadata']['headline'] );
        }
        else
        {
            echo  $details['elements'][0]['tagline']."\n";
            $credential->setSubTitle(  $details['elements'][0]['tagline'] );
        }


        // Add the institutions
        foreach( $details['linked']['partners.v1'] as $university )
        {
            $ins = $this->dbHelper->getInstitutionBySlug( $university['shortName']);
            if($ins)
            {
                $credential->addInstitution( $ins );
            }
            else
            {
                $this->out("University Not Found - " . $university['name']);
            }
        }

        // Add the courses
        foreach($details['linked']['courses.v1'] as $topic )
        {
            $course = $this->dbHelper->getCourseByShortName( 'coursera_' . $topic['slug'] );
            if( $course )
            {
                $credential->addCourse( $course );
            }
            else
            {
               $this->out("Course Not Found - " . $topic['name']);
            }
        }

        // Build the description
        $description = $details['elements'][0]['description'];
        $incentives = $details['elements'][0]['metadata']['incentives'];
        $learningObjectives = '';
        foreach($details['elements'][0]['metadata']['learningObjectives'] as $objective)
        {
            $learningObjectives .= "<li>$objective</li>";
        }
        $recommendedBackground = '';
        foreach($details['elements'][0]['metadata']['recommendedBackground'] as $background)
        {
            $recommendedBackground .= "<li>$background</li>";
        }

        $credential->setDescription(
            "<p>$description</p>" .
            "<h3 class='table-tab-content__title'>Incentives & Benefits</h3><p>$incentives</p>".
            "<h3 class='table-tab-content__title'>What You'll Learn</h3>" ."<p><ul>$learningObjectives</ul></p>".
            "<h3 class='table-tab-content__title'>Recommended Background</h3>" . "<p><ul>$recommendedBackground</ul></p>"
        );

        return $credential;
    }

    private function getCredentialFromSpecialization( $details )
    {
        $credential = new Credential();
        $credential->setName( $details['name'] );
        $credential->setPricePeriod(Credential::CREDENTIAL_PRICE_PERIOD_TOTAL);
        $credential->setPrice(0);
        $credential->setSlug( $details['short_name']. '-specialization' );
        $credential->setInitiative( $this->initiative );
        $credential->setUrl( sprintf(self::SPECIALIZATION_PAGE_URL,$details['short_name'], $details['id']));
        $credential->setOneLiner( $details['subhead']);

        // Add the institutions
        foreach( $details['universities'] as $university )
        {
            $ins = $this->dbHelper->getInstitutionBySlug( $university['short_name']);
            if($ins)
            {
                $credential->addInstitution( $ins );
            }
            else
            {
                $this->out("University Not Found - " . $university['name']);
            }
        }

        // Add the courses
        foreach($details['topics'] as $topic )
        {
            $course = $this->dbHelper->getCourseByShortName( 'coursera_' . $topic['short_name'] );
            if( $course )
            {
                $credential->addCourse( $course );
            }
            else
            {
                $this->out("Course Not Found - " . $topic['name']);
            }
        }

        // Get Description
        $credential->setDescription( $details['byline'] );

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

    /**
     * Wrapper to catch exceptions in file_get_contents
     * @param $url
     */
    private function file_get_contents_wrapper($url)
    {
        try
        {
            return file_get_contents($url);
        } catch (\Exception $e)
        {
            $this->out("file_get_contents_error: " . $e->getMessage());
        }

        return '';
    }

    private function buildCourseraCoursesJson()
    {
        $today = new \DateTime();
        $today = $today->format('Y_m_d');

        $filename = "coursera_$today.json";
        $filePath = '/tmp/'.$filename;

        $courseraCourses = array();
        if(file_exists($filePath))
        {

            $courseraCourses = json_decode($this->file_get_contents_wrapper($filePath),true);
            $this->out("Read from cache");
        }
        else
        {
            $url = self::COURSE_CATALOG_URL_v2;
            $allCourses = json_decode($this->file_get_contents_wrapper( $url ),true);
            foreach ($allCourses['linked']['courses.v1'] as $element)
            {
                if( $element['courseType'] == 'v2.ondemand' || $element['courseType'] == 'v2.capstone')
                {
                    $onDemandCourse =  json_decode($this->file_get_contents_wrapper( sprintf(self::ONDEMAND_COURSE_URL, $element['slug']) ),true);
                    $this->out( $onDemandCourse['elements'][0]['name'] );
                    if( !$onDemandCourse['elements'][0]['isReal'] )
                    {
                        continue; //skip
                    }

                    $courseId = $onDemandCourse['elements'][0]['id'];

                    // Session details
                    $onDemandCourse['sessionDetails'] =  json_decode( $this->file_get_contents_wrapper( sprintf(self::ONDEMAND_SESSION_IDS,$courseId) ),true);

                    // Get Course Details like Syllabus and length
                    $onDemandCourse['courseDetails'] =  json_decode($this->file_get_contents_wrapper( sprintf(self::ONDEMAND_OPENCOURSE_API, $onDemandCourse['elements'][0]['slug']) ),true);

                    // Calculate the length of the course
                    $onDemandCourse['schedule'] = json_decode($this->file_get_contents_wrapper( sprintf(self::ONDEMAND_COURSE_SCHEDULE, $onDemandCourse['elements'][0]['id']) ),true);

                    // $onDemandCourse['productPrices'] = json_decode(file_get_contents( sprintf(self::PRODUCT_PRICES, $element['id']) ),true);;
                    // $onDemandCourse['onDemandCourseMaterials'] = json_decode(file_get_contents( sprintf(self::ONDEMAND_COURSE_MATERIALS, $element['slug']) ),true);
                    
                    $courseraCourses[] = $onDemandCourse;
                }
            }
            file_put_contents($filePath,json_encode($courseraCourses));
        }
        return $courseraCourses;
    }
}