<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 6/30/14
 * Time: 12:01 AM
 */

namespace ClassCentral\SiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SpotlightCopyCommand
 * @package ClassCentral\SiteBundle\Command
 */
class SpotlightCopyCommand extends ContainerAwareCommand {

    public function configure()
    {
        $this
            ->setName('classcentral:spotlight-copy')
            ->setDescription('Copies one spotlight item to another')
            ->addArgument('from', InputArgument::REQUIRED,"From spotlight item id")
            ->addArgument('to', InputArgument::REQUIRED, "To spotlight item id")
        ;
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $fromSpotlightId = intval($input->getArgument('from'));
        $toSpotlightId = intval($input->getArgument('to'));
        $spotlightService = $this->getContainer()->get('spotlight');

        $response = $spotlightService->spotlightCopy($fromSpotlightId, $toSpotlightId);

        $output->writeln("Copied '{$response[0]}' to '{$response[1]}'");

    }


} 