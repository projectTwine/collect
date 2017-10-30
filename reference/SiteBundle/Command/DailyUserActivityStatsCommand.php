<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 8/17/15
 * Time: 4:08 PM
 */

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\SiteBundle\Entity\UserCourse;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DailyUserActivityStatsCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('classcentral:dailyuseractivity')
            ->setDescription('Sends one days worth of user activity to Slack')
            ->addArgument("date", InputArgument::REQUIRED, "Date for which the summary is generated - Y-m-d")
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $date = $input->getArgument('date');

        $dateParts = explode('-', $date);
        if( !checkdate( $dateParts[1], $dateParts[2], $dateParts[0] ) )
        {
            $output->writeLn("<error>Invalid date or format. Correct format is Y-m-d</error>");
            return;
        }

        $dt_start = new \DateTime($date);
        $dt_end = new \DateTime($date);
        $dt_end->add( new \DateInterval('P1D'));

        // Get the User counts
        $userCountQuery = $em->createQueryBuilder();
        $userCountQuery
            ->add('select','count(u.id) as new_users')
            ->add('from','ClassCentralSiteBundle:User u')
            ->Where('u.created >= :created and u.created <= :created_1')
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
            ;

        $newUsers = $userCountQuery->getQuery()->getSingleScalarResult();

        $output->writeln( $newUsers );


        // Get the number of Ratings
        $ratingCountQuery = $em->createQueryBuilder();
        $ratingCountQuery
            ->add('select','count(u.id) as new_reviews')
            ->add('from','ClassCentralSiteBundle:Review u')
            ->Where('u.created >= :created and u.created <= :created_1')
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
        ;

        $newRatings = $ratingCountQuery->getQuery()->getSingleScalarResult();

        $output->writeln( $newRatings );

        // Get the number of Reviews
        $reviewCountQuery = $em->createQueryBuilder();
        $reviewCountQuery
            ->add('select','count(u.id) as new_reviews')
            ->add('from','ClassCentralSiteBundle:Review u')
            ->Where("u.created >= :created and u.created <= :created_1 and u.review != :review")
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
            ->setParameter('review',  '')
        ;

        $newReviews = $reviewCountQuery->getQuery()->getSingleScalarResult();

        $output->writeln( $newReviews );

        // Get the number of courses added to MOOC Tracker
        $userCourseCountQuery = $em->createQueryBuilder();
        $userCourseCountQuery
            ->add('select','count(u.id) as new_mooc_tracker_courses')
            ->add('from','ClassCentralSiteBundle:UserCourse u')
            ->Where('u.created >= :created and u.created <= :created_1')
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
        ;

        $newMOOCTrackerCourses = $userCourseCountQuery->getQuery()->getSingleScalarResult();
        $output->writeln( $newMOOCTrackerCourses );

        // Get the number of courses completed added to MOOC Tracker
        $userCourseCompletedCountQuery = $em->createQueryBuilder();
        $userCourseCompletedCountQuery
            ->add('select','count(u.id) as new_mooc_tracker_courses')
            ->add('from','ClassCentralSiteBundle:UserCourse u')
            ->Where('u.created >= :created and u.created <= :created_1 and u.listId = :completed')
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
            ->setParameter('completed',  UserCourse::LIST_TYPE_COMPLETED);
        ;

        $completedMOOCTrackerCourses = $userCourseCompletedCountQuery->getQuery()->getSingleScalarResult();
        $output->writeln( $completedMOOCTrackerCourses );

        // Get the number of courses interested added to MOOC Tracker
        $userCourseInterestedCountQuery = $em->createQueryBuilder();
        $userCourseInterestedCountQuery
            ->add('select','count(u.id) as new_mooc_tracker_courses')
            ->add('from','ClassCentralSiteBundle:UserCourse u')
            ->Where('u.created >= :created and u.created <= :created_1 and u.listId = :completed')
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
            ->setParameter('completed',  UserCourse::LIST_TYPE_INTERESTED);
        ;

        $interestedMOOCTrackerCourses = $userCourseInterestedCountQuery->getQuery()->getSingleScalarResult();
        $output->writeln( $interestedMOOCTrackerCourses );


        // Credential count query
        $credentialReviewQuery = $em->createQueryBuilder();
        $credentialReviewQuery
            ->add('select','count(u.id) as new_reviews')
            ->add('from','ClassCentralCredentialBundle:CredentialReview u')
            ->Where('u.created >= :created and u.created <= :created_1')
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
        ;

        $newCredentialReviews = $credentialReviewQuery->getQuery()->getSingleScalarResult();
        $output->writeln( $newCredentialReviews );

        // Follows count query
        $followQuery = $em->createQueryBuilder();
        $followQuery
            ->add('select','count(f.id) as new_follows')
            ->add('from','ClassCentralSiteBundle:Follow f')
            ->Where('f.created >= :created and f.created <= :created_1')
            ->setParameter('created', $dt_start)
            ->setParameter('created_1',  $dt_end)
        ;

        $newFollows = $followQuery->getQuery()->getSingleScalarResult();
        $output->writeln( $newFollows );

        // Send it to slack
        $this->getContainer()
            ->get('slack_client')
            ->to('#stats')
            ->send("
            *Stats for $date*
New Users   : $newUsers
New Ratings : $newRatings
New Reviews : $newReviews
New Credential Reviews: $newCredentialReviews
Courses Added to MOOC Tracker : $newMOOCTrackerCourses
Courses marked as Completed : $completedMOOCTrackerCourses
Courses marked as Interested : $interestedMOOCTrackerCourses
New Follows : $newFollows
            ");
    }
}