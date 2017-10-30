<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/25/14
 * Time: 5:28 PM
 */

namespace ClassCentral\SiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Takes in a template and outputs a template inlined uring ZurbInliner
 * @package ClassCentral\SiteBundle\Command
 */
class ZurbInlinerCommand extends ContainerAwareCommand {

    const ZURB_INLINER_ENDPOINT = 'http://zurb.com/ink/skate-proxy.php';

    protected function configure()
    {
        $this
            ->setName('classcentral:zurbinliner')
            ->setDescription('Takes in a template and outputs the inlined version')
            ->addArgument('bundle', InputArgument::REQUIRED,"name of the bundle e.g MOOCTrackerBundle")
            ->addArgument('dir',InputArgument::REQUIRED, "name of the director inside Resources/views e.g. Reminder")
            ->addArgument('src',InputArgument::REQUIRED,"src file name eg. single.course.html")
            ->addArgument('dest',InputArgument::REQUIRED,"dest file nam eg. single.course.inlined.html")
        ;
    }

    protected function execute( InputInterface $input, OutputInterface $output)
    {
        // Get the inuput args
        $bundle = $input->getArgument('bundle');
        $dir = $input->getArgument('dir');
        $src = $input->getArgument('src');
        $dest = $input->getArgument('dest');

        $folder = "src/ClassCentral/{$bundle}/Resources/views/{$dir}";

        $finder = new Finder();
        $fs = new Filesystem();

        // Read a template
        $finder
            ->files()
            ->in($folder)
            ->name($src)
            ;
        foreach($finder as $file)
        {
            $contents = $file->getContents();
            $inlined = $this->inline( $contents );

            // Save the inlined file
            $fs->dumpFile(
                "$folder/$dest",
                $inlined
            );

            $output->writeln("File written in $folder/$dest");
            break;
        }
    }

    private function inline($html)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//        curl_setopt($ch, CURLOPT_HEADER, array(
//            "Content-Type: application/x-www-form-urlencoded"
//        ));
        curl_setopt($ch, CURLOPT_URL, self::ZURB_INLINER_ENDPOINT);
        $encoded = urlencode($html);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "source=$encoded");

        $result = curl_exec($ch);

        curl_close($ch);
        $response = json_decode($result, true);
        return $response['html'];
    }
}