<?php

namespace ClassCentral\SiteBundle\Command\Network;

use ClassCentral\SiteBundle\Command\Network\NetworkAbstractInterface;
use ClassCentral\SiteBundle\Entity\Offering;

class GoogleplusNetwork extends NetworkAbstractInterface
{
    public function outInitiative( $initiative , $offeringCount)
    {
        $this->output->writeln( $this->getBold(strtoupper($initiative->getName()) . "({$offeringCount})"));
    }

    public function beforeOffering()
    {
        return;
    }


    public function outOffering(Offering $offering)
    {
        // Print the title line
        $titleLine = $this->getItalics($offering->getName());
        if($offering->getStatus() == Offering::START_DATES_KNOWN)
        {
            $titleLine .= ' - ' . $offering->getStartDate()->format('M jS');

        }
        $this->output->writeln( $titleLine);

        // Print out the course length. Exclude Udacity because course length is same
        if( $offering->getInitiative()->getCode() != 'UDACITY' && $offering->getLength() != 0)
        {
            $this->output->writeln($this->getItalics( $offering->getLength() . " weeks long"));
        }

        // Output the URL
        $this->output->writeln( $offering->getUrl());

        // Output an empty line
        $this->output->writeln('');

    }

    private function getBold( $text )
    {
        return "*". $text . "*";
    }

    private function getItalics( $text )
    {
        return "_". $text . "_";
    }
}

