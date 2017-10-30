<?php

namespace ClassCentral\SiteBundle\Command\DataMigration;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Query\ResultSetMapping;

abstract class VersionAbstractInterface
{
    /**
     * @var ContainerInterface
     */
    protected  $container;
    
    /**
     * @var OutputInterface
     * @param 
     */
    protected $output;
    
    protected $version;

    public function __construct(ContainerInterface $container, OutputInterface $output)
    {
        $this->container = $container;
        $this->output =  $output;
    }
   
    public function setVersion( $version ) {
        $this->version = $version;
    }
    /** 
     * Checks whether the migration has been run before
     */
    public function isExecuted() {
         $em = $this->container->get('Doctrine')->getManager();
         $rsm = new ResultSetMapping();
         $rsm->addScalarResult('executed', 'executed');
         $query = $em->createNativeQuery("SELECT executed FROM datamigrations WHERE version=?",$rsm);
         $query->setParameter(1,$this->version);
         
         $result = $query->getResult();         
         
         return empty($result) ? false : $result[0]['executed'];
         
    }
    
    public function hasBeenExecuted() {
        $conn= $this->container->get('Doctrine')->getManager()->getConnection();
        $conn->executeQuery("INSERT INTO datamigrations(version, executed) VALUES({$this->version},1)");
        
    }

    abstract public function migrate();
    
}
