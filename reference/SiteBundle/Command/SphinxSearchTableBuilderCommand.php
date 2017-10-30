<?php

namespace ClassCentral\SiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Entity\Sphinxsearch;
use ClassCentral\SiteBundle\Network\NetworkFactory;

class SphinxSearchTableBuilderCommand extends ContainerAwareCommand {
    
    private $search = '';
    protected function configure()
    {
        $this
            ->setName('classcentral:sphinxsearch')
            ->setDescription('Generate table used for sphinx search');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Empty the sphinxsearch table
        $this->getContainer()->
          get('database_connection')->
          query("TRUNCATE sphinxsearch");
        
        $em = $this->getContainer()->get('Doctrine')->getManager();
        $offeringRepository = 
                $this->getContainer()->get('Doctrine')
                     ->getRepository('ClassCentralSiteBundle:Offering');
        
        $allOfferings = $offeringRepository->findAll();
        
        foreach($allOfferings as $offering)
        {
            if($offering->getStatus() == Offering::COURSE_NA) {
                continue;
            }
            $course = $offering->getCourse();
            $this->search = '';
            $sphinx = new Sphinxsearch();
            
            $sphinx->setId($offering->getId());
            
            $this->buildSearchString( $offering->getName() );
            $sphinx->setName( $offering->getName() );
            
            $initiative = $course->getInitiative();
            if(!empty($initiative))
            {
                $this->buildSearchString( $course->getInitiative()->getName() );
                $sphinx->setInitiative( $course->getInitiative()->getName() );
            }
            
            $this->buildSearchString( $offering->getCourse()->getStream()->getName() );
            $sphinx->setStream( $course->getStream()->getName() );
            
            foreach($course->getInstructors() as $instructor)
            {
                $this->buildSearchString( $instructor->getName() );
            }

            foreach($course->getInstitutions() as $institution)
            {
                $this->buildSearchString( $institution->getName() );
            }
           
            $this->buildSearchString($course->getSearchDesc());
            
            $sphinx->setSearch($this->search);           
            
            $em->persist($sphinx);
           
        }
         $em->flush();
    }
    
    private function buildSearchString( $text )
    {
        $this->search .= ' ' . $text;
    }
        
    
}

?>
