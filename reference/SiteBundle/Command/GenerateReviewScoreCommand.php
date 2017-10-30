<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 6/7/15
 * Time: 8:33 PM
 */

namespace ClassCentral\SiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateReviewScoreCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('classcentral:reviews:score')
            ->setDescription('Updates the score (sort order) for all reviews')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $rs = $this->getContainer()->get('review');
        $limit = 1000;
        $offset = 0;
        $reviewsUpdated = 0;
        $reviewsExamined = 0;

        $reviews = $em->getRepository('ClassCentralSiteBundle:Review')->findBy(
            array(), array(), $limit, $offset
        );

        while( $reviews )
        {

            foreach($reviews as $review)
            {
                $reviewsExamined++;
                $score = $rs->scoreReview( $review );
                if ( $score != $review->getScore() )
                {
                    $output->writeln("Review Id {$review->getId()} received a score of $score");
                    $review->setScore( $score );
                    $em->persist($review);
                    $reviewsUpdated++;
                }

            }

            $em->flush();
            $offset += $limit;
            unset( $reviews );

            $reviews = $em->getRepository('ClassCentralSiteBundle:Review')->findBy(
                array(), array(), $limit, $offset
            );
            $output->writeln("Processed $offset reviews");
        }

        $em->flush();
        $output->writeln("Reviews Examined : " . $reviewsExamined);
        $output->writeln("Reviews Updated : " . $reviewsUpdated);
    }
} 