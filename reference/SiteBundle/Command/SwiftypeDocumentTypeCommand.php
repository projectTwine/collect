<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 8:11 PM
 */

namespace ClassCentral\SiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ClassCentral\SiteBundle\Swiftype\SwiftypeIndexer;

class SwiftypeDocumentTypeCommand extends ContainerAwareCommand {

    private $task = array('create, delete');
    private $type = array('courses','universities','providers','subjects');
    protected function configure()
    {
        $this->setName('classcentral:swiftype:doctype')
             ->setDescription("Create or delete a doctype. eg courses, universities, providers, subjects")
             ->addArgument('task',InputArgument::REQUIRED,"create or delete")
             ->addArgument('type',InputArgument::REQUIRED,"Which Doctype? eg courses, universities, providers, subjects")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $task = $input->getArgument('task');
        $type = $input->getArgument('type');

        if(!in_array($task,$this->task) && !in_array($type, $this->type))
        {
            $output->writeln("Invalid task or type.");
            return;
        }

        // Get the indexer
        $token = $this->getContainer()->getParameter('swiftype_api_key');
        $engine = $this->getContainer()->getParameter('swiftype_engine_slug');
        $indexer = new SwiftypeIndexer($token, $engine);

        if($task == 'create')
        {
            $result = $indexer->createDocumentType($type);
        }
        else
        {
            $result = $indexer->deleteDocumentType($type);
        }

        $output->writeln("$task for $type finished");
        $output->writeln($result);
    }
} 