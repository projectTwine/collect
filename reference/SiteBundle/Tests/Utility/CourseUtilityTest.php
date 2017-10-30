<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 3/23/14
 * Time: 9:55 PM
 */

namespace ClassCentral\SiteBundle\Tests\Utility;


use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Utility\CourseUtility;
use Symfony\Component\Validator\Constraints\DateTime;

class CourseUtilityTest extends  \PHPUnit_Framework_TestCase {


    public function testCalculateState()
    {
        $now = new \DateTime();
        $future = new \DateTime();
        $future->add(new \DateInterval('P200D'));
        $past = new \DateTime();
        $past->sub(new \DateInterval('P200D'));
        $recent = new \DateTime();
        $recent->add(new \DateInterval('P7D'));


        $offering = new Offering();
        $offering->setCreated($now);
        $offering->setStartDate($future);
        $offering->setStatus(Offering::START_DATES_KNOWN);

        $state = CourseUtility::calculateStateWithDate($offering, $now);



        // Test recent + upcoming
        $offering = $this->getOffering($past, $recent, $future);
        $state = CourseUtility::calculateStateWithDate($offering, $now);
        $this->assertEquals(
            Offering::STATE_RECENT + Offering::STATE_UPCOMING,
            $state,
            "State is not recent and upcoming"
        );

        // Test just announced + upcoming
        $offering = $this->getOffering($now, $future, $future);
        $state = CourseUtility::calculateStateWithDate($offering, $now);
        $this->assertEquals(
            Offering::STATE_JUST_ANNOUNCED + Offering::STATE_UPCOMING,
            $state,
            "State is not just announced and upcoming"
        );

        // Test finished
        $offering = $this->getOffering($past, $past, $past);
        $state = CourseUtility::calculateStateWithDate($offering, $now);
        $this->assertEquals(
            Offering::STATE_FINISHED,
            $state,
            "State is not finished"
        );

        // Test on going
        $offering = $this->getOffering($past, $past, $future);
        $state = CourseUtility::calculateStateWithDate($offering, $now);
        $this->assertEquals(
            Offering::STATE_IN_PROGRESS,
            $state,
            "State is not in progress/ongoing"
        );

        // Test self paced
        $offering = $this->getOffering($past, $past, $past);
        $offering->setStatus(Offering::COURSE_OPEN);
        $state = CourseUtility::calculateStateWithDate($offering, $now);    
        $this->assertEquals(
            Offering::STATE_SELF_PACED,
            $state,
            "State is not self faced"
        );

    }

    private function getOffering($created, $starDate, $endDate)
    {
        $offering = new Offering();
        $offering->setCreated($created);
        $offering->setStartDate($starDate);
        $offering->setEndDate($endDate);
        $offering->setStatus(Offering::START_DATES_KNOWN);

        return $offering;
    }


    public function testGetStates()
    {
        // upcoming
        $states = CourseUtility::getStatesFromState(Offering::STATE_UPCOMING);
        $this->assertNotEmpty(
            array_intersect(array('upcoming'), $states),
            'Get states should have returned upcoming'
        );

        // recent + upcoming
        $states = CourseUtility::getStatesFromState(Offering::STATE_UPCOMING + Offering::STATE_RECENT);
        $this->assertNotEmpty(
            array_intersect(array('upcoming','recent'), $states),
            'Get states should have returned upcoming and recent'
        );

        // recent + just announced + upcoming
        $states = CourseUtility::getStatesFromState(Offering::STATE_UPCOMING + Offering::STATE_RECENT + Offering::STATE_JUST_ANNOUNCED);
        $this->assertNotEmpty(
            array_intersect(array('upcoming','recent','recentlyAdded'), $states),
            'Get states should have returned upcoming, recent, and recentlyAdded'
        );


        // self paced
        $states = CourseUtility::getStatesFromState(Offering::STATE_SELF_PACED);
        $this->assertNotEmpty(
            array_intersect(array('selfpaced'), $states),
            'Get states should have returned selfpaced'
        );

        // ongoing
        $states = CourseUtility::getStatesFromState(Offering::STATE_IN_PROGRESS);
        $this->assertNotEmpty(
            array_intersect(array('ongoing'), $states),
            'Get states should have returned ongoing/inprogress'
        );

        // finished
        $states = CourseUtility::getStatesFromState(Offering::STATE_FINISHED);
        $this->assertNotEmpty(
            array_intersect(array('past'), $states),
            'Get states should have returned finished'
        );

    }


    public function testGetNextSession()
    {

        $c = new Course();
        // Finished offerings
        $fo1 = $this->buildOffering(1, new \DateTime("2012-06-03"), Offering::START_DATES_KNOWN );
        $fo2 = $this->buildOffering(2, new \DateTime("2012-05-03"), Offering::START_DATES_KNOWN );
        $fo3 = $this->buildOffering(3, new \DateTime("2012-07-03"), Offering::START_DATES_KNOWN );

        // Ongoing session
        $oodt1 = new \DateTime();
        $oodt1->sub( new \DateInterval("P10D") );
        $oo1 = $this->buildOffering(4, $oodt1, Offering::START_DATES_KNOWN);


        // Self paced session
        $so1 = $this->buildOffering(5, new \DateTime("2012-07-03"), Offering::COURSE_OPEN);

        // Upcoming sessions
        $uo1 = $this->buildOffering(6, $this->getFutureDateUtility("P20D"), Offering::START_DATES_KNOWN );
        $uo2 = $this->buildOffering(7, $this->getFutureDateUtility("P10D"), Offering::START_DATES_KNOWN );
        $uo3 = $this->buildOffering(8, $this->getFutureDateUtility("P30D"), Offering::START_DATES_KNOWN );

        // Course with single finished offering
        $c->addOffering($fo1);
        $next = CourseUtility::getNextSession($c);
        $this->assertEquals($fo1->getId(),$next->getId());

        // Course with multiple finished offering
        $c->addOffering($fo2);
        $c->addOffering($fo3);
        $next = CourseUtility::getNextSession($c);
        $this->assertEquals($fo3->getId(),$next->getId());

        // Course with ongoing sessions
        $c->addOffering($oo1);
        $next = CourseUtility::getNextSession($c);
        $this->assertEquals($oo1->getId(),$next->getId());

        // Course with with self paced sessions
        $c->addOffering($so1);
        $next = CourseUtility::getNextSession($c);
        $this->assertEquals($so1->getId(),$next->getId());

        // Course with single upcoming session
        $c->addOffering($uo1);
        $next = CourseUtility::getNextSession($c);
        $this->assertEquals($uo1->getId(),$next->getId());

        // Course with multiple upcoming sessions
        $c->addOffering($uo2);
        $c->addOffering($uo3);
        $next = CourseUtility::getNextSession($c);
        $this->assertEquals($uo2->getId(),$next->getId());
    }

    private function getFutureDateUtility( $interval )
    {
        $dt = new \DateTime();
        $dt->add(new \DateInterval($interval));
        return $dt;
    }

    private function buildOffering($id, $date, $state)
    {
        $o = new Offering();
        $o->setId($id);
        $o->setStartDate( $date );

        // End date
        $endDate = clone $date;
        $endDate->add(new \DateInterval("P1M"));
        $o->setEndDate($endDate);
        $o->setStatus( $state );
        return $o;
    }

} 