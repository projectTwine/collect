<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/31/16
 * Time: 1:50 PM
 */

namespace ClassCentral\SiteBundle\Command\Network;


use ClassCentral\SiteBundle\Entity\Offering;

class TableNetwork extends NetworkAbstractInterface
{

    public function outInitiative($initiative, $offeringCount)
    {
        $name   = strtoupper($initiative->getName());
        $url = "https://www.class-central.com/subject/". $initiative->getSlug();


        $this->output->writeln("</tbody></table><h2><a href='$url'>$name ($offeringCount)</a></h2><table width='85%' align='center'><tbody>");
    }

    public function beforeOffering()
    {
        // TODO: Implement beforeOffering() method.
    }

    public function outOffering(Offering $offering)
    {
        $formatter = $this->container->get('course_formatter');
        echo $formatter->tableRowFormat( $offering->getCourse() ) ."\n";
    }
}