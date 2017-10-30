<?php

namespace ClassCentral\SiteBundle\Command\Network;
 
use Symfony\Component\Console\Output\OutputInterface;

class NetworkFactory
{

    public static function get($network,OutputInterface $output)
    {
        if(empty($network))
        {
            $network = 'Default';
        }
        $network = ucwords( $network );
        $class = "ClassCentral\\SiteBundle\\Command\\Network\\" . $network . "Network";        
        $obj = new $class($output);
        return $obj;
    }
}
