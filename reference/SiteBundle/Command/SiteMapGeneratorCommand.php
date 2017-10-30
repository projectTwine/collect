<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 10/12/16
 * Time: 11:49 PM
 */

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\CredentialBundle\Entity\Credential;
use ClassCentral\SiteBundle\Controller\InitiativeController;
use ClassCentral\SiteBundle\Controller\InstitutionController;
use ClassCentral\SiteBundle\Controller\LanguageController;
use ClassCentral\SiteBundle\Controller\StreamController;
use ClassCentral\SiteBundle\Entity\CourseStatus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Genearates sitemap.txt which contains a list of all urls
 * Class SiteMapGeneratorCommand
 * @package ClassCentral\SiteBundle\Command
 */
class SiteMapGeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classcentral:sitemap:generate')
            ->setDescription("Generate Sitemap");
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $router = $this->getContainer()->get('router');
        $baseUrl = $this->getContainer()->getParameter('baseurl');

        $sitemap = fopen("web/sitemap.txt", "w");

        // List all the courses first
        $courses = $em->getRepository('ClassCentralSiteBundle:Course')->findAll();
        foreach ($courses as $course)
        {
            // The course is valid
            if($course->getStatus() < CourseStatus::COURSE_NOT_SHOWN_LOWER_BOUND)
            {
                $coursePageUrl = $baseUrl . $router->generate(
                      'ClassCentralSiteBundle_mooc',
                       array('id' => $course->getId(),'slug'=>$course->getSlug())
                    );
                fwrite($sitemap,$coursePageUrl."\n");
            }
        }

        // CREDENTIALS
        fwrite($sitemap,$baseUrl. $router->generate('credentials')."\n");
        $credentials = $em->getRepository('ClassCentralCredentialBundle:Credential')->findAll();
        foreach($credentials as $credential)
        {
            if($credential->getStatus() < Credential::CREDENTIAL_NOT_SHOWN_LOWER_BOUND)
            {
                $credentialPageUrl = $baseUrl . $router->generate(
                        'credential_page',
                        array('slug'=>$credential->getSlug())
                    );
                fwrite($sitemap,$credentialPageUrl."\n");
            }
        }

        // Subjects
        fwrite($sitemap,$baseUrl. $router->generate('subjects')."\n");
        $streamController = new StreamController();
        $subjects = $streamController->getSubjectsList($this->getContainer());
        foreach($subjects['parent'] as $subject)
        {
            $subjectPageUrl =
                $baseUrl . $router->generate(
                    'ClassCentralSiteBundle_stream',
                    array('slug'=>$subject['slug'])
                );
            fwrite($sitemap,$subjectPageUrl."\n");
        }
        foreach($subjects['children'] as $childSubjects)
        {
            foreach( $childSubjects as $subject)
            {
                $subjectPageUrl =
                    $baseUrl . $router->generate(
                        'ClassCentralSiteBundle_stream',
                        array('slug'=>$subject['slug'])
                    );
                fwrite($sitemap,$subjectPageUrl."\n");
            }
        }


        // Providers
        fwrite($sitemap,$baseUrl. $router->generate('providers')."\n");
        $providerController = new InitiativeController();
        $providers = $providerController->getProvidersList($this->getContainer());
        foreach($providers['providers'] as $provider)
        {
            if($provider['count'] > 0)
            {
                $providerPageUrl =
                    $baseUrl . $router->generate(
                        'ClassCentralSiteBundle_initiative',
                        array('type'=>$provider['code'])
                    );
                fwrite($sitemap,$providerPageUrl."\n");
            }
        }

        // Universities/Institutions
        fwrite($sitemap,$baseUrl. $router->generate('institutions')."\n");
        $insController = new InstitutionController();
        $institutions = $insController->getInstitutions( $this->getContainer(), false);
        $institutions = $institutions['institutions'];
        foreach($institutions as $institution)
        {
            if($institution['count'] > 0)
            {
                $insPageUrl =
                    $baseUrl . $router->generate(
                        'ClassCentralSiteBundle_institution',
                        array('slug'=>$institution['slug'])
                    );
                fwrite($sitemap,$insPageUrl."\n");
            }

        }
        // Get Universities
        fwrite($sitemap,$baseUrl. $router->generate('universities')."\n");
        $universities = $insController->getInstitutions( $this->getContainer(), true);
        $universities = $universities['institutions'];
        foreach($universities as $university)
        {
            if($university['count']>0)
            {
                $uniPageUrl =
                    $baseUrl . $router->generate(
                        'ClassCentralSiteBundle_university',
                        array('slug'=>$university['slug'])
                    );
                fwrite($sitemap,$uniPageUrl."\n");
            }

        }

        // Languages
        fwrite($sitemap,$baseUrl. $router->generate('languages')."\n");
        $langController = new LanguageController();
        $languages = $langController->getLanguagesList($this->getContainer());
        foreach($languages as $language)
        {
            $langPageUrl =
                $baseUrl . $router->generate(
                    'lang',
                    array('slug'=>$language->getSlug())
                );
            fwrite($sitemap,$langPageUrl."\n");
        }

        // Tags
        $tags = $em->getRepository('ClassCentralSiteBundle:Tag')->findAll();
        foreach ($tags as $tag)
        {
            if(empty($tag->getName())) continue;
            $tagPageUrl =
                $baseUrl . $router->generate(
                    'tag_courses',
                    array('tag'=>urlencode($tag->getName()))
                );
            fwrite($sitemap,$tagPageUrl."\n");
        }

        fclose($sitemap);
    }
}