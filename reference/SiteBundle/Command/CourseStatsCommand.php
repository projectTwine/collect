<?php

namespace ClassCentral\SiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ClassCentral\SiteBundle\Network\NetworkFactory;

class CourseStatsCommand extends ContainerAwareCommand{
    
    protected function configure() {
          $this
            ->setName('classcentral:coursestats');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output){

//        $stats = $this
//            ->getContainer()->get('doctrine')
//            ->getManager()
//            ->getRepository('ClassCentralSiteBundle:Offering')
//            ->courseStats();
//
//        print_r($stats);
//
//        $totalCount = 0;
//        ksort($stats);
//        foreach($stats as $year => $months)
//        {
//            ksort($months);
//            foreach($months as $month => $count)
//            {
//                $totalCount += $count;
//                // echo sprintf("%s-%s,%d",$year,$month,$totalCount)."\n";
//                echo sprintf("%d",$totalCount)."\n";
//            }
//        }
//
//        exit();



        $liveCourses = $this
            ->getContainer()->get('doctrine')
            ->getManager()
            ->getRepository('ClassCentralSiteBundle:Offering')
            ->getAllLiveCourses();

        $offeredStats = array();
        foreach($liveCourses as $course)
        {
            $offeredStats[count($course->getOfferings())]++;
        }

        $output->writeln("Total Courses " . count($liveCourses));
        print_r($offeredStats);

        $universities = array();
        $institutions = array();
        $instructors = array();
        $lang = array();
        $subjects = array();
        $providers = array();


        foreach($liveCourses as $course)
        {
            foreach($course->getInstructors() as $instructor)
            {
                $instructors[] = $instructor->getId();
            }

            foreach($course->getInstitutions() as $institution)
            {
                if($institution->getIsUniversity())
                {
                    $universities[] = $institution->getId();
                }
                else
                {
                    $institutions[] = $institution->getId();
                    //echo $institution->getName(). " --- ";
                }
            }

            // Languages
            if($course->getLanguage())
            {
                $lang[$course->getLanguage()->getName()]++;
            }

            $streamName = $course->getStream()->getName();
            if($course->getStream()->getParentStream())
            {
                $streamName = $course->getStream()->getParentStream()->getName();
            }

            $subjects[$streamName]++;

            if($course->getInitiative())
            {
                $providers[$course->getInitiative()->getName()]++;
            }
            else
            {
                $providers['Others']++;
            }
        }

        echo "\nUniversities : " . count(array_unique($universities)). "\n";
        echo "Institutions:  " . count(array_unique($institutions)). "\n";
        echo "Instructors:   " . count(array_unique($instructors)). "\n";

        var_dump($lang);
        foreach($lang as $name => $count)
        {
            $output->writeln("$name,$count");
        }
        var_dump($subjects);
        foreach($subjects as $name => $count)
        {
            $output->writeln("$name,$count");
        }

        var_dump($providers);
        foreach($providers as $name => $count)
        {
            $output->writeln("$name,$count");
        }

    }

}