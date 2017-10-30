<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dhawal
 * Date: 3/23/13
 * Time: 11:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace ClassCentral\ScraperBundle\Command;


use ClassCentral\SiteBundle\Entity\Initiative;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ClassCentral\ScraperBundle\Scraper\ScraperFactory;

class ScraperCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("classcentral:scrape")
            ->setDescription("Scrapes courses")
            ->addArgument('initiative',InputArgument::REQUIRED,"Initiative code")
            ->addOption('simulate',null,InputOption::VALUE_OPTIONAL,"N if database needs to be modified. Defaults to Y") // value is Y or N
            ->addOption('type',null,InputOption::VALUE_OPTIONAL,"'add' - create offerings. 'update' - update already created offerings. Defaults to update")
            ->addOption("credential",null,InputOption::VALUE_OPTIONAL,"If true, scrape credentials")
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $initiativeCode = $input->getArgument('initiative');
        // Check if initiative exists
        $initiative = $this->getContainer()
                        ->get('doctrine')
                        ->getManager()
                        ->getRepository('ClassCentralSiteBundle:Initiative')
                        ->findOneBy(array('code' => $initiativeCode));
        if($initiative == null)
        {
            $output->writeln("Invalid initiative code $initiativeCode");
            return;
        }

        $simulate = $input->getOption("simulate");
        if(empty($simulate) || $simulate != 'N')
        {
            $simulate = 'Y';
        }

        $type = $input->getOption("type");
        if(empty($type) || $type != 'add')
        {
            $type = 'update';
        }
        $credential = $input->getOption("credential");
        $credential = (isset($credential)) ? $credential : false;
        // Initiate the factory
        $scraperFactory = new ScraperFactory($initiative);
        $scraperFactory->setSimulate($simulate);
        $scraperFactory->setType($type);
        $scraperFactory->setIsCredential( $credential );
        $scraperFactory->setOutputInterface($output);
        $scraperFactory->setContainer($this->getContainer());


        $scraper = $scraperFactory->getScraper();
        $startTime = microtime(true);
        $this->sendMessageToSlack("Scraper Started. Simulate: $simulate; Type = $type" , $initiative);
        $scraper->scrape();
        $time_elapsed_secs = microtime(true) - $startTime;
        $this->sendMessageToSlack("Scraper Ended. Took $time_elapsed_secs seconds", $initiative);
        $output->writeln("Scraper Ended. Took $time_elapsed_secs seconds");
    }

    private function sendMessageToSlack($msg, Initiative $provider)
    {
        $logo = $this->getContainer()->getParameter('rackspace_cdn_base_url') . $provider->getImageUrl() ;

        $this->getContainer()
            ->get('slack_client')
            ->to('#cc-activity-data')
            ->from( $provider->getName() )
            ->withIcon( $logo )
            ->send( $msg );
    }

}