<?php

namespace ClassCentral\SiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ClassCentral\SiteBundle\Command\DataMigration\DataMigrationFactory;

/**
 * This command is used to migrate data after schema changes. Each version should be run exactly once
 */
class DataMigrationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classcentral:datamigrate')
            ->setDescription('Migrate Data')
            ->addArgument('version', InputArgument::REQUIRED,"Which version");           
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = DataMigrationFactory::get($input->getArgument('version'), $this->getContainer(), $output);
        if( !$version->isExecuted() ) {
            $version->migrate();
            $version->hasBeenExecuted();
        } else {
            $output->writeln("Migration has already been executed");
        }
    }

}
