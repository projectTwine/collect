<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 11/2/13
 * Time: 10:25 PM
 */
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class VerificationTokenTest extends PHPUnit_Framework_TestCase
{
    private $vService;
    private $em;
    private $doctrine;
    private $repository;
    private $validToken;
    private $expiredToken;

    public static function setUpBeforeClass()
    {

    }

    public function setUp()
    {
       $this->repository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository', array('findOneByToken'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager', array('persist', 'flush','remove','getRepository'))
            ->disableOriginalConstructor()->getMock();
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(true));
        $this->em
            ->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(true));
        $this->em
            ->expects($this->any())
            ->method('remove')
            ->will($this->returnValue(true));
        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));
        $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry', array('geManager'))
                                ->disableOriginalConstructor()->getMock();
        $this->doctrine
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));



        $this->vService = new \ClassCentral\SiteBundle\Services\VerificationToken($this->doctrine);
    }


    public function testCreatesAtoken()
    {
          $vToken1 = $this->vService->create("test_user_id");
          $vToken2 = $this->vService->create("test_user_id",300);

          $this->assertNotEmpty($vToken1->getToken());
          $this->assertEquals('test_user_id',$vToken1->getValue());
          // Check if expiry is one week
          $this->assertEquals(\ClassCentral\SiteBundle\Entity\VerificationToken::EXPIRY_1_WEEK,$vToken1->getExpiry());
          $this->assertEquals(300,$vToken2->getExpiry());
    }

    public function testTokenExpiry()
    {
        $vtoken = $this->vService->create("test_user_id");
        $this->assertTrue($this->vService->isValid($vtoken));

        $this->assertTrue($this->vService->isValid($this->getValidToken()));
        $this->assertFalse($this->vService->isValid($this->getExpiredToken()));
    }

    /*
    public function testTokenRetrivial()
    {
        $validToken = $this->getValidToken();
        $ValidTokenString = $validToken->getToken();
        $this->repository->expects($this->at(0))
            ->method('findOneByToken')
            ->with( $this->stringContains($ValidTokenString))
            ->will($this->returnValue($validToken));

        $this->assertNotNull($this->vService->get($ValidTokenString));
        $this->assertEquals($ValidTokenString, $this->vService->get($ValidTokenString)->getToken());
    }
    */


    private function getValidToken()
    {
        $vtoken = $this->vService->create("test_user_id");
        $expiredDate = new \DateTime();
        $expiredDate->sub(new \DateInterval("PT" . (\ClassCentral\SiteBundle\Entity\VerificationToken::EXPIRY_1_WEEK -100) . "M"));
        $vtoken->setCreated($expiredDate);

        return $vtoken;
    }

    public function getExpiredToken()
    {
        $vtoken = $this->vService->create("test_user_id");
        $notExpired = new \DateTime();
        $notExpired->sub(new \DateInterval("PT" . (\ClassCentral\SiteBundle\Entity\VerificationToken::EXPIRY_1_WEEK + 100) . "M"));
        $vtoken->setCreated($notExpired);

        return $vtoken;
    }


}
 