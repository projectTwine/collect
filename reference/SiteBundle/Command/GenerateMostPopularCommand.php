<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 7/30/17
 * Time: 3:23 AM
 */

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\SiteBundle\Entity\CourseStatus;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Utility\CourseUtility;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a list of ten most popular MOOCs
 * Class GenerateMostPopularCommand
 * @package ClassCentral\SiteBundle\Command
 */
class GenerateMostPopularCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('classcentral:mostpopular')
            ->setDescription('Generates a list of ten most popular MOOCs')
            ->addArgument('month', InputArgument::OPTIONAL,"Which month")
            ->addArgument('year', InputArgument::OPTIONAL, "Which year")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $month = $input->getArgument('month');
        $year = $input->getArgument('year');
        $dt = new \DateTime;
        if (!$month) {
            $month = $dt->format('m');
        }
        if (!$year) {
            $year = $dt->format('Y');
        }

        $em  = $this->getContainer()->get('doctrine')->getManager();
        $coursesByCount = array();

        // Get Courses added in the last 45 days
        $query = $em->createQueryBuilder();
        $created = new \DateTime();
        $created->sub(new \DateInterval("P45D"));

        $query->add('select', 'c')
            ->add('from', 'ClassCentralSiteBundle:Course c')
            ->andWhere('c.status = :status')
            ->andWhere('c.created > :created')
            ->setParameter('status', 0)
            ->setParameter('created',$created->format('Y-m-d'));
        $recentCourses = $query->getQuery()->getResult();

        foreach ($recentCourses as $recentCourse)
        {

            if($recentCourse->getInitiative() && $recentCourse->getInitiative()->getName() == 'Coursera')
            {
                $courseId = $recentCourse->getId();
                $timesAdded = $this->getContainer()->get('Cache')->get('course_interested_users_' . $courseId, function ($courseId){
                    return $this->getContainer()->get('doctrine')->getManager()->getRepository('ClassCentralSiteBundle:Course')->getInterestedUsers( $courseId );
                }, array($courseId));

                $coursesByCount[$courseId] = $timesAdded;
            }
        }

        // Get courses starting in the month given
        $query = $em->createQueryBuilder();

        $query->add('select', 'o')
            ->add('from', 'ClassCentralSiteBundle:Offering o')
            ->add('orderBy', 'o.startDate ASC')
            ->andWhere('o.status != :status')
            ->andWhere('MONTH(o.startDate) = :month')
            ->andWhere('YEAR(o.startDate) = :year')
            ->setParameter('status', Offering::COURSE_NA)
            ->setParameter('month',$month)
            ->setParameter('year',$year);

        $sessions = $query->getQuery()->getResult();
        foreach ($sessions as $session)
        {
            $course = $session->getCourse();

            if($course->getStatus() != CourseStatus::AVAILABLE
                || $course->getPrice() != 0
                || $session->getStatus() == Offering::START_DATES_UNKNOWN
                || $session->getStatus() == Offering::COURSE_OPEN)
            {
                continue;
            }

            $courseId = $course->getId();
            $timesAdded = $this->getContainer()->get('Cache')->get('course_interested_users_' . $courseId, function ($courseId){
                return $this->getContainer()->get('doctrine')->getManager()->getRepository('ClassCentralSiteBundle:Course')->getInterestedUsers( $courseId );
            }, array($courseId));
            $timesOffered = 0;
            foreach($course->getOfferings() as $o)
            {
                $states = CourseUtility::getStates( $o );
                if( in_array( 'past', $states) || in_array( 'ongoing', $states) )
                {
                    $timesOffered++;
                }
            }
            if ($timesOffered <1 )
            {
                $coursesByCount[$course->getId()] = $timesAdded;
            }
        }

        arsort($coursesByCount);

        $formatter = $this->getContainer()->get('course_formatter');
        $repo = $em->getRepository('ClassCentralSiteBundle:Course');
        $i= 0;

        foreach($coursesByCount as $courseId => $count)
        {
            $c = $repo->find($courseId );
            echo $formatter->blogFormat( $c ) . "\n";
            $i++;
            if($i == 20) break;
        }

        $output->writeln("");$output->writeln("");

        $i= 0;
        foreach($coursesByCount as $courseId => $count)
        {
            $c = $repo->find($courseId );
            echo $formatter->emailFormat( $c ) . "\n";
            $i++;
            if($i == 20) break;
        }
    }

}