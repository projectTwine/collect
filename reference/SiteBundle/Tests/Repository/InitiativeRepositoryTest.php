<?php

namespace ClassCentral\SiteBundle\Tests\Repository;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InitiativeRepositoryTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testGetAllInitiatives()
    {
        $initiatives = $this->em
            ->getRepository('ClassCentralSiteBundle:Initiative')
            ->findAll();

        $this->assertTrue(count($initiatives) > 0);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        //$this->em->close();
    }
}