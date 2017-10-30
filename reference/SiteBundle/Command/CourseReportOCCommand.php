<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 10/23/14
 * Time: 5:15 PM
 */

namespace ClassCentral\SiteBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a list in OpenCulture formatting
 * Class CourseReportOCCommand
 * @package ClassCentral\SiteBundle\Command
 */
class CourseReportOCCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('classcentral:openculture')
            ->setDescription('Generates a Course Report to match open cultures formatting. Can be used only for upcoming courses')
            ->addArgument('month', InputArgument::OPTIONAL,"Which month")
            ->addArgument('year', InputArgument::OPTIONAL, "Which year")

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $esCourses = $this->getContainer()->get('es_courses');
        $em = $this->getContainer()->get('doctrine')->getManager();

        $month = $input->getArgument('month');
        $year = $input->getArgument('year');
        $dt = new \DateTime;
        if (!$month) {
            $month = $dt->format('m');
        }
        if (!$year) {
            $year = $dt->format('Y');
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start = new \DateTime("$year-$month-01");
        $end = new \DateTime("$year-$month-$daysInMonth");

        $results = $esCourses->findByNextSessionStartDate($start, $end);

        $courseraSkipped = 0;

        foreach($results['results']['hits']['hits'] as $course)
        {
            $c = $course['_source'];
            $newCourse = false;
            if($c['provider']['name'] == 'Coursera')
            {
                if(count($c['sessions']) > 4)
                {
                    $courseraSkipped++;
                    continue;
                }

                if( count($c['sessions']) == 1)
                {
                    $newCourse = true;
                }
            }

            $output->writeln(  $this->getHtml($c, $newCourse) );
        }
        $output->writeln( $courseraSkipped);
        $output->writeLn($results['results']['hits']['total'] - $courseraSkipped. " courses");
    }

    private function getHtml( $course, $newCourse = false )
    {
        $format = '<li>%s<a href="%s">%s</a> (%s) -%s %s - %s %s</li>';

        // Course Name
        $name = trim($course['name']);

        $newCourseText = '';
        if($newCourse)
        {
            $newCourseText = '[New]';
        }

        // Course Url
        $url  = $course['nextSession']['url'];

        // Name of the Institution
        $institutionName = '';
        if( !empty($course['institutions']) )
        {
            $ins = array_pop($course['institutions']);
            $institutionName = ' ' .$ins['name'] . ' on';
        }

        $provider = $course['provider']['name'];
        if($provider == 'EdX')
        {
            $provider = 'edX';
        }

        // Certificate
        $certs = array();
        if($provider == 'Coursera')
        {
            if($course['certificate'])
            {
                $certs[] = 'SA';

            }
            if($course['verifiedCertificate'])
            {
                $certs[] = 'VC$';
            }
        }

        if($provider == 'edX')
        {
            $certs[] = 'HCC';
            if($course['verifiedCertificate'])
            {
                $certs[] = 'VC$';
            }
        }


        if($provider == 'FutureLearn')
        {
            $certs[] = 'SP$';
        }

        $cert  = implode('/',$certs);

        $dt = new \DateTime( $course['nextSession']['startDate']);
        $date = $dt->format('F j');

        $length ='' ;
        if( $course['length'] == 0)
        {
            // Do nothing
        }
        else
        {
            $length = "({$course['length']} weeks)";
        }

        return sprintf($format,$newCourseText, $url,$name,$cert, $institutionName, $provider, $date, $length);
    }
}