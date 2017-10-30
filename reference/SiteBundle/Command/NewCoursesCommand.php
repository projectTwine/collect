<?php

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\SiteBundle\Entity\Offering;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class NewCoursesCommand
 * Gets all new courses since the specified date. If no date is
 * specified, it retrieves the courses in last 2 weeks
 * @package ClassCentral\SiteBundle\Command
 */
class NewCoursesCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName("classcentral:newcourses")
            ->setDescription("Generates a list of courses in last 2 weeks")
            ->addArgument('date', InputArgument::OPTIONAL,"Which date? eg. mm/dd/yyyy");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $router = $this->getContainer()->get('router');
        $date = $input->getArgument('date');
        $formatter = $this->getContainer()->get('course_formatter');

        if($date)
        {

            $dt = \DateTime::createFromFormat("m/d/Y",$date) ;
        }
        else
        {
            // Nothing specified. Pick a date 2 weeks ago
            $dt = new \DateTime();
            $dt->sub(new \DateInterval("P14D"));
        }

        $courses = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('ClassCentralSiteBundle:Course')
            ->getNewCourses($dt);



        $groups = array();
        foreach($courses as $course)
        {
            if($course->getStatus() >= 100)
            {
                // Course is not available or is under review
                continue;
            }

            if( !$course->getIsMooc() )
            {
                continue;
            }

            $subject = $course->getStream();
            if($subject->getParentStream())
            {
                $subject = $subject->getParentStream();
            }

            $groups[$subject->getName()][] = $course;

        }


        $count = 0;
        foreach($groups as $insName => $insCourses)
        {
            $output->writeln("</tbody></table><h2><b>" . strtoupper($insName)."</h2></b><br/><table width='85%' align='center'><tbody>");
            foreach($insCourses as $course)
            {

                $count++;
                echo $formatter->tableRowFormat($course);
            }

            $output->writeln( "<br/>");
        }

        $output->writeLn( " $count courses added " );
}
}