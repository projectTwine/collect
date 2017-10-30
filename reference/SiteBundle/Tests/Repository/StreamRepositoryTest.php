<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/2/13
 * Time: 4:20 PM
 */

namespace ClassCentral\SiteBundle\Tests\Repository;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StreamRepositoryTest extends WebTestCase {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testGetCoursesCountBySubjects()
    {
        $subjects = $this->em->getRepository('ClassCentralSiteBundle:Stream')->getCourseCountBySubjects();
        foreach($subjects as $id => $subject)
        {
            $this->assertEquals($id, $subject['id']);
            $this->assertNotNull($subject['courseCount']);
        }

    }

} 