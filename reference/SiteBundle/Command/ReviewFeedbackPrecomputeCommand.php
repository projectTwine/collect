<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/28/14
 * Time: 5:04 PM
 */

namespace ClassCentral\SiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Precomputes the per review feedback.
 * i.e 3 out of 5 people found this review helpful
 * Class ReviewFeedbackPrecomputeCommand
 * @package ClassCentral\SiteBundle\Command
 */
class ReviewFeedbackPrecomputeCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('classcentral:reviews:precomputefeedback');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('review_id','review_id');
        $rsm->addScalarResult('positive','positive');
        $rsm->addScalarResult('negative','negative');
        $rsm->addScalarResult('total','total');

        $q = $em->createNativeQuery(
            "SELECT review_id,
                SUM(if(helpful =0,1,0)) as negative,
                SUM(if (helpful =1,1,0)) as positive,
                count(*) as total FROM reviews_feedback
                GROUP BY review_id; ", $rsm
        );
        $results = $q->getResult();

        // Iterate through the array and update the feedback summary table
        foreach($results as $result)
        {
            $n = $result['negative'];
            $p = $result['positive'];
            $t  = $result ['total'];
            $id = $result['review_id'];

            $query = sprintf("INSERT INTO reviews_feedback_summary(review_id,positive,negative,total)
                                VALUES(%d,%d,%d,%d)
                            ON DUPLICATE KEY UPDATE
                                positive = %d,
                                negative = %d,
                                total = %d
                            ", $id, $p,$n,$t,$p,$n,$t);

            $em->getConnection()->exec($query);
        }
    }
} 