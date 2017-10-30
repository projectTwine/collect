<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/19/14
 * Time: 1:12 AM
 */

namespace ClassCentral\SiteBundle\Command\DataMigration;
use ClassCentral\SiteBundle\Entity\Course;

/***
 * Class Version6
 * Migration to add appropriate codes to edX
 * @package ClassCentral\SiteBundle\Command\DataMigration
 */
class Version6 extends VersionAbstractInterface {

    const EDX_COURSE_LIST_CSV = "/tmp/edx.csv";

    public function migrate()
    {
        $em = $this->container->get('Doctrine')->getManager();
        $cr = $em->getRepository('ClassCentralSiteBundle:Course');

        $csv = file_get_contents(self::EDX_COURSE_LIST_CSV);
        $file = fopen(self::EDX_COURSE_LIST_CSV, 'r');

        fgetcsv($file); // Skip the Header
        $notFound = 0;
        while( !feof($file) )
        {
            $c = $this->getEdxArray( fgetcsv($file) );
            if( empty($c['name']) ) continue; // skip the last line
            $edxCourseId = $this->getEdxCourseId( $c['url'] );
            $sn = 'edx_' . strtolower( $c['code'] );
            $newSn = 'edx_' . strtolower( $c['code'] . '_' . $c['school'] );

            $courseFound = false;

            // Find by short name
            $course = $cr->findOneBy( array(
                'shortName' => $sn
            ));
            if( $course)
            {
                $courseFound = true;
                //$this->output->writeln( $c['name'] );
                // Update the edx code
                $this->updateShortName($course,$newSn);

            }
            if ($courseFound) continue;

            // Find course by name
            $course = $cr->findOneBy( array(
                'name' => $c['name']
            ));

            if( $course)
            {
                $courseFound = true;
                //$this->output->writeln( $c['name'] );
                // Update the edx code
                $this->updateShortName($course,$newSn);

            }

            if ($courseFound) continue;

            // Find using code+ name
            $course = $cr->findOneBy( array(
                'name' => $c['code'] . ': ' .$c['name']
            ));

            if( $course)
            {
                $courseFound = true;
                //$this->output->writeln( $c['name'] );
                // Update the edx code
                $this->updateShortName($course,$newSn);

            }
            if ($courseFound) continue;

            // Find using code+ name but remove the x
            $course = $cr->findOneBy( array(
                'name' => substr($c['code'], 0, -1) . ': ' .$c['name']
            ));

            if( $course)
            {
                $courseFound = true;
                //$this->output->writeln( $c['name'] );
                // Update the edx code
                $this->updateShortName($course,$newSn);

            }
            if ($courseFound) continue;

            $query = $em->createQueryBuilder();
            $query
                ->add('select', 'c')
                ->add('from','ClassCentralSiteBundle:Course c')
                ->andWhere('c.name LIKE  :cname')
                ->setParameter('cname', '%' . $c['name'] . '%')
                ;
            $course = $query->getQuery()->getResult();
            if( $course )
            {
                $courseFound = true;
                //$this->output->writeln( $c['name'] );
                // Update the edx code
                $this->updateShortName($course[0],$newSn);

            }
            if ($courseFound) continue;

            // Not found
            $notFound++;
            $this->output->writeln(  $c['code'] . ': ' .$c['name'] );

        }

        $this->output->writeln( "Not found - $notFound");
        $em->flush();
    }

    private function updateShortName(Course $course, $sn)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $course->setShortName( $sn );
        $em->persist( $course );
    }

    private function getEdxArray( $line )
    {
        $c = array();
        $c['school'] = $line['4'];
        $c['name'] = trim($line[5]);
        $c['code'] = $line[6];
        $c['startDate'] = $line[8];
        $c['endDate'] = $line[9];
        $c['url'] = $line[10];

        $c['description'] = $line[13];

        return $c;
    }

    private function getEdxCourseId($url)
    {
        return substr($url, strrpos($url,'-')+1);
    }
}