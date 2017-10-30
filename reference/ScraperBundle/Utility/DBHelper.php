<?php

/**
 * Contains a bunch of utility functions to save and retrieve information
 * from the database
 */
namespace ClassCentral\ScraperBundle\Utility;


use ClassCentral\CredentialBundle\Entity\Credential;
use ClassCentral\ScraperBundle\Scraper\ScraperAbstractInterface;
use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Institution;
use ClassCentral\SiteBundle\Entity\Instructor;
use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Entity\Stream;
use ClassCentral\SiteBundle\Services\Kuber;
use ClassCentral\SiteBundle\Utility\PageHeader\PageHeaderFactory;
use Doctrine\ORM\EntityManager;

class DBHelper
{

    private $scraper;

    public function setScraper(ScraperAbstractInterface $scraper)
    {
        $this->scraper = $scraper;
    }

    /**
     * Creates a course if it does not exist
     * @param $name Name of the course
     * @param Initiative $initiative
     * @param Institution $ins
     * @return Course
     */
    public function createCourseIfNotExists($name, Initiative $initiative, Institution $ins = null, Stream $stream)
    {
        // Check if course exists
        $em = $this->scraper->getManager();
        $courseRepository = $em->getRepository('ClassCentralSiteBundle:Course');
        $course = $courseRepository->findOneBy(array(
            'name' => $name,
            'initiative' => $initiative->getId(),
        ));

        // Course exists
        if ($course)
        {
            return $course;
        }

        $course = new Course();
        $course->setName($name);
        $course->setInitiative($initiative);
        if ($ins)
        {
            $course->addInstitution($ins);
        }

        $course->setStream($stream);

        // Check if course is to be created
        if ($this->scraper->doModify() && $this->scraper->doCreate())
        {
            $em->persist($course);
            $em->flush();

            $this->scraper->out("COURSE $name created for initiative " . $initiative->getName());
        }

        return $course;

    }

    public function createInstructorIfNotExists($name)
    {
        $em = $this->scraper->getManager();
        $instructor = $em->getRepository('ClassCentralSiteBundle:Instructor')->findOneBy(
            array('name' => $name)
        );
        if ($instructor)
        {
            return $instructor;
        }

        $instructor = new Instructor();
        $instructor->setName($name);
        if($this->scraper->doCreate())
        {
            $this->scraper->out("NEW INSTRUCTOR $name");
            if ($this->scraper->doModify())
            {
                $em->persist($instructor);
                $em->flush();
            }
        }

        return $instructor;
    }

    public function createInstitutionIfNotExists(Institution $institution)
    {
        $em = $this->scraper->getManager();
        $ins = $em->getRepository('ClassCentralSiteBundle:Institution')->findOneBy(array(
            'slug' => $institution->getSlug(),
        ));

        if($ins)
        {
            // Institution exists
            return $ins;
        }

        if ($this->scraper->doCreate())
        {
            $this->scraper->out("NEW INSTITUTION:  {$institution->getName()}");
            if ($this->scraper->doModify() )
            {
                $em->persist($institution);
                $em->flush();

            }
        }
        return $institution;
    }

    public function getStreamBySlug($slug = 'cs')
    {
        $em = $this->scraper->getManager();
        $stream = $em->getRepository('ClassCentralSiteBundle:Stream')->findOneBy(array(
            'slug' => $slug,
        ));
        return $stream;
    }

    /**
     * A map between language_name => ClassCentralSiteBundle:Language
     */
    public function getLanguageMap()
    {
        $em = $this->scraper->getManager();
        $languages = $em->getRepository('ClassCentralSiteBundle:Language')->findAll();
        $languageMap = array();
        foreach($languages as $language)
        {
            $languageMap[$language->getName()] = $language;
        }

        return $languageMap;
    }

    public function getOfferingByShortName($shortName)
    {
        $em = $this->scraper->getManager();
        $offering = $em->getRepository('ClassCentralSiteBundle:Offering')->findOneBy(array(
                        'shortName' => $shortName
                    ));
        return $offering;
    }


    public function getOfferingByUrl($url)
    {
        $em = $this->scraper->getManager();
        $offering = $em->getRepository('ClassCentralSiteBundle:Offering')->findOneBy(array(
            'url' => $url
        ));
        return $offering;
    }

    public function getCourseByShortName($shortName)
    {
        $em = $this->scraper->getManager();
        $course = $em->getRepository('ClassCentralSiteBundle:Course')->findOneBy(array(
            'shortName' => $shortName
        ));
        return $course;
    }

    public function getInstitutionBySlug( $slug )
    {
        $em = $this->scraper->getManager();
        $ins = $em->getRepository('ClassCentralSiteBundle:Institution')->findOneBy(array(
            'slug' => $slug
        ));

        return $ins;
    }

    public function getInstitutionByName( $name )
    {
        $em = $this->scraper->getManager();
        $ins = $em->getRepository('ClassCentralSiteBundle:Institution')->findOneBy(array(
            'name' => $name
        ));

        return $ins;
    }

    public  function findCourseByName ($title, Initiative $initiative)
    {
        $em = $this->scraper->getManager();
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

    public function sendNewCourseToSlack( Course $course, Initiative $initiative)
    {
        try
        {
        $providerInfo = PageHeaderFactory::get( $initiative );
        $coursePageUrl = $this->scraper->getContainer()->getParameter('baseurl'). $this->scraper->getContainer()->get('router')->generate('ClassCentralSiteBundle_mooc',
                array('id' => $course->getId(), 'slug' => $course->getSlug() ));
        $logo = $this->scraper->getContainer()->getParameter('rackspace_cdn_base_url') . $providerInfo->getImageUrl() ;
        $message ="[New Course] *{$course->getName()}*\n" .$coursePageUrl ;
        $this->scraper->getContainer()
            ->get('slack_client')
            ->to('cc-activity-data')
            ->from( $initiative->getName() )
            ->withIcon( $logo )
            ->send( $message );
        }
        catch(\Exception $e)
        {

        }
    }

    public function sendNewOfferingToSlack(Offering $offering )
    {
        try
        {
            $course = $offering->getCourse();
            $initiative = $offering->getInitiative();

            $providerInfo = PageHeaderFactory::get( $initiative );
            $coursePageUrl = $this->scraper->getContainer()->getParameter('baseurl'). $this->scraper->getContainer()->get('router')->generate('ClassCentralSiteBundle_mooc',
                    array('id' => $course->getId(), 'slug' => $course->getSlug() ));
            $logo = $this->scraper->getContainer()->getParameter('rackspace_cdn_base_url') . $providerInfo->getImageUrl() ;
            $message ="[New Session] *{$course->getName()}* -  {$offering->getDisplayDate()}\n" .$coursePageUrl ;
            $this->scraper->getContainer()
                ->get('slack_client')
                ->to('cc-activity-data')
                ->from( $initiative->getName() )
                ->withIcon( $logo )
                ->send( $message );
        }
        catch(\Exception $e)
        {

        }
    }

    public function getCredentialBySlug($slug)
    {
        $em = $this->scraper->getManager();
        $credential = $em->getRepository('ClassCentralCredentialBundle:Credential')->findOneBy(array(
            'slug' => $slug
        ));
        return $credential;
    }

    public function uploadCredentialImageIfNecessary( $imageUrl, Credential $credential, $extension = null)
    {
        $kuber = $this->scraper->getContainer()->get('kuber');
        $uniqueKey = basename($imageUrl);
        if( $kuber->hasFileChanged( Kuber::KUBER_ENTITY_CREDENTIAL,Kuber::KUBER_TYPE_CREDENTIAL_IMAGE, $credential->getId(),$uniqueKey ) )
        {
            // Upload the file
            $filePath = '/tmp/credential_'.$uniqueKey;
            file_put_contents($filePath,file_get_contents($imageUrl));
            $kuber->upload(
                $filePath,
                Kuber::KUBER_ENTITY_CREDENTIAL,
                Kuber::KUBER_TYPE_CREDENTIAL_IMAGE,
                $credential->getId(),
                $extension,
                $uniqueKey
            );

        }
    }


    public function changedFields($fields, $entity, $dbEntity)
    {
        $changedFields = array();
        foreach($fields as $field)
        {
            $getter = 'get' . $field;
            $setter = 'set' . $field;
            if($entity->$getter() != $dbEntity->$getter())
            {
                $courseModified = true;

                // Add the changed field to the changedFields array
                $changed = array();
                $changed['field'] = $field;
                $changed['old'] =$dbEntity->$getter();
                $changed['new'] = $entity->$getter();
                $changedFields[] = $changed;

                $dbEntity->$setter($entity->$getter());
            }

        }

        return $changedFields;
    }

    public function outputChangedFields($changedFields)
    {
        foreach($changedFields as $changed)
        {
            $field = $changed['field'];
            $old = is_a($changed['old'], 'DateTime') ? $changed['old']->format('jS M, Y') : $changed['old'];
            $new = is_a($changed['new'], 'DateTime') ? $changed['new']->format('jS M, Y') : $changed['new'];

            $this->scraper->out("$field changed from - '$old' to '$new'");
        }
    }
}