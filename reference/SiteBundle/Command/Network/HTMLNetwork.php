<?php

namespace ClassCentral\SiteBundle\Command\Network;

use ClassCentral\SiteBundle\Command\Network\NetworkAbstractInterface;
use ClassCentral\SiteBundle\Entity\Offering;

class HTMLNetwork extends NetworkAbstractInterface
{
    public function outInitiative( $stream , $offeringCount)
    {

          $name   = strtoupper($stream->getName());
          $url = "https://www.class-central.com/subject/". $stream->getSlug();


        $this->output->writeln("<h2><a href='$url'>$name ($offeringCount)</a></h2>");
    }

    public function beforeOffering()
    {
        return;
    }

    public function outOffering(Offering $offering)
    {
        $formatter = $this->container->get('course_formatter');
        echo $formatter->blogFormat( $offering->getCourse() ) ."\n";
    }
}
