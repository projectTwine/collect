<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 8:59 PM
 */

namespace ClassCentral\SiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ClassCentral\SiteBundle\Swiftype\SwiftypeIndexer;
use ClassCentral\SiteBundle\Swiftype\DocumentBuilderFactory;

class SwiftypeDocumentIndexerCommand extends ContainerAwareCommand {

    private $type = array('courses','universities','providers',"subjects");

    protected function configure()
    {
        $this->setName('classcentral:swiftype:index')
            ->setDescription("Index a particular doctype. eg courses, universities, providers,subjects")
            ->addArgument('type',InputArgument::REQUIRED,"Which Doctype? eg courses, universities, providers,subjects")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        if(!in_array($type, $this->type))
        {
            $output->writeln("Invalid type.");
            return;
        }

        // Get the indexer
        $token = $this->getContainer()->getParameter('swiftype_api_key');
        $engine = $this->getContainer()->getParameter('swiftype_engine_slug');
        $indexer = new SwiftypeIndexer($token, $engine);

        $docBuilder = DocumentBuilderFactory::getDocumentBuilder($this->getContainer(),$type);
        $allDocs = $docBuilder->getDocuments();
        $numDocs = count( $allDocs);
        $totalIndexed = 0;
        $batch_size = 100;
        while($totalIndexed < $numDocs)
        {
            $docs = array_slice($allDocs,$totalIndexed,$batch_size);
            $result = $indexer->bulkCreateOrUpdate($docs,$type);
            $totalIndexed += $batch_size;
            if(count(array_unique($result)) == 1)
            {
                $output->writeLn(count($result) . " documents indexed successfully");
            }
            else
            {
                $output->writeLn("Some documents may have failed indexing. Total attempted " . count($result));
            }
        }

    }
} 