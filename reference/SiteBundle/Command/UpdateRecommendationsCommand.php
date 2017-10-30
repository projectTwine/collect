<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 2/24/14
 * Time: 4:13 PM
 */

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\SiteBundle\Entity\CourseRecommendation;
use ClassCentral\SiteBundle\Entity\CourseStatus;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates the recommendations tables
 * Class UpdateRecommendationsCommand
 * @package ClassCentral\SiteBundle\Command
 */
class UpdateRecommendationsCommand extends ContainerAwareCommand {

    private $courses = array();
    const RECOMMENDATIONS_SCORE = 0.5;

    protected function configure()
    {
        $this
            ->setName('classcentral:recommender:update')
            ->setDescription('Updates the recommendations from extras/recommendations.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')
            ->getManager();

       // Truncate the recommendations table
//        $conn = $em->getConnection()
//                ->exec("TRUNCATE courses_recommendations");

        $courses = $em->getRepository('ClassCentralSiteBundle:Course')->findAll();

        foreach($courses as $course)
        {
            if($course->getStatus() == CourseStatus::NOT_AVAILABLE) continue;

            // Delete previous recommendations
            $em->getConnection()->exec("DELETE FROM courses_recommendations WHERE course_id=" . $course->getId());

            // Check if recommendations already exist
            // $recos = $em->getRepository('ClassCentralSiteBundle:CourseRecommendation')->findBy(array('course'=>$course));
            // if($recos) continue; // recommendations already exists

            $output->writeln($course->getName());
            // 1. Get all the users interested in the course
            // 2. Get the most popular courses for the interested courses
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('course_id','course_id');
            $results = $em->createNativeQuery("SELECT course_id, count(*) FROM users_courses WHERE user_id IN(SELECT user_id FROM users_courses WHERE course_id = {$course->getId()}) GROUP BY course_id ORDER BY count(*) DESC LIMIT 10;", $rsm)->getResult();
            $position = 1;
            array_shift($results); // Remove the first element which is the course itself
            foreach($results as $result)
            {
                $rc =  $em->getRepository('ClassCentralSiteBundle:Course')->find($result['course_id']);
                if(!$rc || $rc->getStatus() == CourseStatus::NOT_AVAILABLE) continue;
                $r = new CourseRecommendation();
                $r->setCourse($course);
                $r->setRecommendedCourse($rc);
                $r->setPosition( $position );
                $em->persist($r);
                $position++;
            }
            $em->flush();
        }

    }
} 