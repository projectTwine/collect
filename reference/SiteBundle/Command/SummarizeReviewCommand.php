<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/31/15
 * Time: 7:33 PM
 */

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\SiteBundle\Services\Review;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SummarizeReviewCommand extends ContainerAwareCommand {


    /**
     * Review Type:
     * course - summarizes a review for the course
     * review - summarizes a review
     * all    - summarizes all un-summarized reviews
     */
    protected function configure()
    {
        $this
            ->setName('classcentral:reviews:summarize')
            ->setDescription('Summarize a review or all the reviews for a course')
            ->addArgument('type', InputArgument::REQUIRED,"course, review, new, or all")
            ->addArgument('id',InputArgument::REQUIRED,"course id or review id. ignored for all")
        ;
    }


    protected function execute( InputInterface $input, OutputInterface $output)
    {
        $reviewService = $this->getContainer()->get('review');
        $type = strtolower($input->getArgument('type'));
        $id   =  intval( $input->getArgument('id') );

        if( !in_array( $type, array('course','review','all','new')) )
        {
            $output->writeln( "<error>type should be either review, course, or all</error>" );
            return;
        }

        if( $type == 'course' )
        {
            $course = $this->getContainer()
                ->get('doctrine')->getManager()
                ->getRepository('ClassCentralSiteBundle:Course')
                ->find( $id );
            if (!$course)
            {
                // Course does not exist
                $output->writeln( "<error>Invalid course id $id</error>" );
            }
            else
            {
                // Create reviews
                $output->writeln("<info>Summarizing reviews for course - " . $course->getName() ."</info>");
                $response = $reviewService->summarizeReviewsForACourse($course);
                $output->writeln( "Number of Reviews Summarized: $response");
            }

        }
        else if( $type == 'review')
        {
            // type is review
            $review = $this->getContainer()
                ->get('doctrine')->getManager()
                ->getRepository('ClassCentralSiteBundle:Review')
                ->find( $id );

            if (!$review)
            {
                // Course does not exist
                $output->writeln( "<error>Invalid review id $id</error>" );
            }
            else
            {
                $this->summarizeReview($review, $output);
            }


        }
        else if( $type == 'all')
        {
            // Summarize all reviews

            // Find all reviews which need to be summarized
            $query = $this->getContainer()->get('doctrine')->getManager()->createQueryBuilder();
            $query
                ->add('select', 'r')
                ->add('from', 'ClassCentralSiteBundle:Review r')
                ->where('r.reviewSummary is  NULL AND LENGTH(r.review) > 30')
            ;
            $result = $query->getQuery()->getResult();
            $reviewsToBeSummarized = count( $result );
            $output->writeln("<info>Summarising $reviewsToBeSummarized reviews</info>");
            foreach($result as $review)
            {
                $this->summarizeReview($review, $output);
            }
        }
        else if( $type == 'new')
        {
            // Summarize reviews that are more than a day old
            $dt = new \DateTime();
            $dt->sub( new \DateInterval('P1D') );
            // Find all reviews which need to be summarized
            $query = $this->getContainer()->get('doctrine')->getManager()->createQueryBuilder();
            $query
                ->add('select', 'r')
                ->add('from', 'ClassCentralSiteBundle:Review r')
                ->where('r.reviewSummary is  NULL AND LENGTH(r.review) > 30  AND r.created >= :date')
                ->setParameter('date',$dt);
            ;
            $result = $query->getQuery()->getResult();
            $reviewsToBeSummarized = count( $result );
            $output->writeln("<info>Summarising $reviewsToBeSummarized reviews</info>");
            foreach($result as $review)
            {
                $this->summarizeReview($review, $output);
            }
        }
    }

    private function summarizeReview(\ClassCentral\SiteBundle\Entity\Review $review, OutputInterface $output )
    {
        $reviewService = $this->getContainer()->get('review');
        $response = $reviewService->summarizeReview( $review );

        switch ($response)
        {
            case Review::REVIEW_ALREADY_SUMMARIZED_OR_EMPTY_TEXT:
                $output->writeln( "Review already summarized");
                break;
            case Review::REVIEW_SUMMARY_FAILED:
                $output->writeln("Review summary failed for id {$review->getId()} ");
                break;
            default:
                $output->writeln("$response Summaries saved for review with id {$review->getId()} ");
        }
    }

} 