<?php

namespace ClassCentral\SiteBundle\Command\DataMigration;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DataMigrationFactory
{

    public static function get($version,  ContainerInterface $container, OutputInterface $output)
    {
        if(empty($version))
        {
            throw new Exception('Invalid data migration version');
        }
        
        $class = "ClassCentral\\SiteBundle\\Command\\DataMigration\\"  . "Version" . $version;
        $obj = new $class($container, $output);
        $obj->setVersion($version);
        return $obj;
    }
}
