<?php

namespace ClassCentral\SiteBundle\Command;

use ClassCentral\SiteBundle\Command\Network\RedditNetwork;
use ClassCentral\SiteBundle\Entity\CourseStatus;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Utility\CourseUtility;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ClassCentral\SiteBundle\Command\Network\NetworkFactory;

class CourseReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classcentral:coursereport')
            ->setDescription('Generates Course Report')
            ->addArgument('month', InputArgument::OPTIONAL,"Which month")
            ->addArgument('year', InputArgument::OPTIONAL, "Which year")
            ->addOption('network',null, InputOption::VALUE_OPTIONAL)
            ->addOption('cs',null, InputOption::VALUE_OPTIONAL, "Yes/No - Splits the cs courses up by different levels")
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $month = $input->getArgument('month');
        $year = $input->getArgument('year');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $network = NetworkFactory::get( $input->getOption('network'),$output);
        $network->setContainer($this->getContainer());
        $isReddit = ($input->getOption('network') == 'Reddit') || ($input->getOption('cs') == 'Yes');
        $courseToLevelMap = RedditNetwork::getCourseToLevelMap();

        $offerings = $this
            ->getContainer()->get('doctrine')
            ->getManager()
            ->getRepository('ClassCentralSiteBundle:Offering')
            ->courseReport($month, $year);
        
        
        // Segreagate by Initiative 
        $offeringsByInitiative = array();
        $offeringsByStream = array();
        $offeringsByLevel = array(
            'beginner' => array(),
            'intermediate' => array(),
            'advanced' => array(),
            'uncategorized' => array()
        );

        $newOfferings = array();
        $totalCourses = 0;
        $courseraSkipped = 0;
        $selfPacedSkipped = 0;
        foreach($offerings as $offering)
        {
            if($offering->getInitiative() == null)
            {
                $initiative = 'Others';
            }
            else
            {
                $initiative = $offering->getInitiative()->getName();
            }

            // Skip unapproved courses
            if($offering->getCourse()->getStatus() != CourseStatus::AVAILABLE)
            {
                continue;
            }

            if($offering->getStatus() == Offering::START_DATES_UNKNOWN)
            {
                continue;
            }
            if($offering->getCourse()->getPrice() != 0)
            {
                continue;
            }



            // Skip self paced courses
            if($offering->getStatus() == Offering::COURSE_OPEN)
            {

                continue;
                // Check if the course is less than month old
                // Check if the course is a new course
                $course = $offering->getCourse();
                $oneMonthAgo = new \DateTime();
                $oneMonthAgo->sub(new \DateInterval("P60D"));
                $newCourse = false;
                if($course->getCreated() >= $oneMonthAgo)
                {
                    $newCourse = true;
                }

                if(!$newCourse)
                {
                    $selfPacedSkipped++;
                    continue;
                }
            }

            if($offering->getCourse()->getLanguage() && $offering->getCourse()->getLanguage()->getName() != 'English')
            {
                // continue;
            }
            $totalCourses++;

            // Check if its a Coursera course.
            if($offering->getCourse()->getInitiative() && $offering->getCourse()->getInitiative()->getName() == 'Coursera')
            {
                if(count($offering->getCourse()->getOfferings()) > 6)
                {
                    $courseraSkipped++;
                    //continue;
                }
            }



            $offeringsByInitiative[$initiative][] = $offering;
            $subject = $offering->getCourse()->getStream();
            if($subject->getParentStream())
            {
                $subject = $subject->getParentStream();
            }
            $offeringsByStream[$subject->getName()][] = $offering;

            if($isReddit && ($subject->getName() == 'Computer Science' || $subject->getName() == 'Programming'))
            {
                $totalCourses++;
                $courseId = $offering->getCourse()->getId();
                if(isset($courseToLevelMap[$courseId]))
                {
                    $offeringsByLevel[$courseToLevelMap[$courseId]][] = $offering;
                }
                else
                {
                    $offeringsByLevel['uncategorized'][] = $offering;
                }
            }
        }

        // Segregate by Stream
        $network->setRouter($this->getContainer()->get('router'));

        $coursesByCount = array();

        if($isReddit)
        {
            foreach($offeringsByLevel as $level => $offerings)
            {
                $count = count($offerings);
                $network->outLevel(ucfirst($level), $count);
                $network->beforeOffering();

                uasort($offerings,function($o1,$o2){
                    $rs = $this->getContainer()->get('review');
                    $o1reviews = $rs->getReviews($o1->getCourse()->getId());
                    $o1numRatings = $o1reviews['ratingCount'];
                    if($o1numRatings < 20 && $o1->getCourse()->isCourseNew())
                    {
                        $o1numRatings = 25;
                    }

                    $o2reviews = $rs->getReviews($o2->getCourse()->getId());
                    $o2numRatings = $o2reviews['ratingCount'];
                    if($o2numRatings < 20 && $o2->getCourse()->isCourseNew())
                    {
                        $o2numRatings = 25;
                    }


                    return $o1numRatings < $o2numRatings;
                });

                foreach($offerings as $offering)
                {
                    $network->outOffering( $offering );
                    // Count the number of times its been added to my courses
                    $added = $em->getRepository('ClassCentralSiteBundle:UserCourse')->findBy(array('course' => $offering->getCourse()));
                    $timesOffered = 0;
                    foreach($offering->getCourse()->getOfferings() as $o)
                    {
                        $states = CourseUtility::getStates( $o );
                        if( in_array( 'past', $states) || in_array( 'ongoing', $states) )
                        {
                            $timesOffered++;
                        }
                    }
                    if ($timesOffered <2 )
                    {
                        $timesAdded = count($added);
                        $coursesByCount[$offering->getCourse()->getName()] = $timesAdded;
                    }

                }
            }
        }
        else
        {
            foreach($offeringsByStream as $stream => $offerings)
            {
                $subject = $offerings[0]->getCourse()->getStream();

                if($subject->getParentStream())
                {
                    $subject = $subject->getParentStream();
                }
                $count = count($offerings);
                $network->outInitiative($subject, $count);
                $network->beforeOffering();

                foreach($offerings as $offering)
                {
                    $network->outOffering( $offering );
                    // Count the number of times its been added to my courses
                    $added = $em->getRepository('ClassCentralSiteBundle:UserCourse')->findBy(array('course' => $offering->getCourse()));
                    $timesOffered = 0;
                    foreach($offering->getCourse()->getOfferings() as $o)
                    {

                        $states = CourseUtility::getStates( $o );
                        if( in_array( 'past', $states) || in_array( 'ongoing', $states) )
                        {
                            $timesOffered++;
                        }
                    }
                    if ($timesOffered < 1 )
                    {
                        $timesAdded = count($added);
                        $coursesByCount[$offering->getCourse()->getId()] = $timesAdded;
                        $newOfferings[] = $offering;
                    }
                    else
                    {
                        // Check if the course is less than month old
                        // Check if the course is a new course
                        $course = $offering->getCourse();
                        $oneMonthAgo = new \DateTime();
                        $oneMonthAgo->sub(new \DateInterval("P60D"));
                        $newCourse = false;
                        if($course->getCreated() >= $oneMonthAgo)
                        {
                            $newCourse = true;
                        }
                        // Is it being offered for he first time
                        if(count($course->getOfferings()) == 1 and $offering->getCreated() > $oneMonthAgo  )
                        {
                            $newCourse = true;
                        }
                        if(count($course->getOfferings()) == 1 and $offering->getStatus() != Offering::COURSE_OPEN )
                        {
                            $newCourse = true;
                        }

                        if($newCourse)
                        {
                            $newOfferings[] = $offering;
                        }
                    }
                }
            }
        }

        // Output new offerings
        echo count($newOfferings). ' courses';

//        $network->beforeOffering();
//        foreach($newOfferings as $newOffering)
//        {
//            $network->outOffering( $newOffering );
//        }


        echo "<br/> Total Courses: " . $totalCourses;
        echo "<br/>" . $courseraSkipped;
        echo "<br/>" . $selfPacedSkipped;

//        arsort($coursesByCount);
//
//        $formatter = $this->getContainer()->get('course_formatter');
//        $repo = $this->getContainer()->get('doctrine')->getManager()->getRepository('ClassCentralSiteBundle:Course');
//        $i= 0;
//
//        foreach($coursesByCount as $courseId => $count)
//        {
//            $c = $repo->find($courseId );
//            echo $formatter->blogFormat( $c ) . "\n";
//            $i++;
//            if($i == 20) break;
//        }
//
//        print_r($coursesByCount);

    }



}
