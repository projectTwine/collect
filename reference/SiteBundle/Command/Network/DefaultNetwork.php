<?php

namespace ClassCentral\SiteBundle\Command\Network;

use ClassCentral\SiteBundle\Command\Network\NetworkAbstractInterface;
use ClassCentral\SiteBundle\Entity\Offering;

class DefaultNetwork extends NetworkAbstractInterface
{
    public function outInitiative( $initiative , $offeringCount)
    {
        if($initiative == null) 
        {
            $name = 'Others';
        }
        else
        {
           $name = $initiative->getName(); 
        }

       $this->output->writeln(strtoupper($name) . "({$offeringCount})"); 
    }

    public function beforeOffering()
    {
        return;
    }

    public function outOffering(Offering $offering)
    {
        // Print the title line
        $titleLine =  $offering->getName();
        if($offering->getStatus() == Offering::START_DATES_KNOWN)
        {
            $titleLine .= ' - ' . $offering->getStartDate()->format('M jS');

        }
        $this->output->writeln( $titleLine);
        $this->output->writeln($offering->getUrl());

        $this->output->writeln(' ');

    }
}

