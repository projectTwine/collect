<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 3/23/14
 * Time: 9:23 PM
 */

namespace ClassCentral\SiteBundle\Utility;


use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\Offering;

class CourseUtility {

    /**
     * Calculates the state of the offering - i.e recent, upcoming etc
     * @param Offering $offering
     */
    public static function calculateState(Offering $offering)
    {
        return self::calculateStateWithDate($offering, new \DateTime());
    }

    /**
     * By default the offering state is upcoming;
     * @param Offering $offering
     * @param \DateTime $now
     * @return int|string
     */
    public static function calculateStateWithDate(Offering $offering, \DateTime $now)
    {
        $state = 0;

        // Ignore offerings that are no longer available
        if($offering->getStatus() == Offering::COURSE_NA)
        {
            return $state;
        }

        $twoWeeksAgo = clone $now;
        $twoWeeksAgo->sub(new \DateInterval('P14D'));
        $twoWeeksLater = clone $now;
        $twoWeeksLater->add(new \DateInterval('P14D'));
        $yesterday = clone $now;
        $yesterday->sub(new \DateInterval('P1D'));
        $fourWeeksAgo = clone $now;
        $fourWeeksAgo->sub(new \DateInterval('P28D'));

        $startDate = $offering->getStartDate();
        $endDate = $offering->getEndDate();

        // Check if its recent
        if (($offering->getStatus() == Offering::START_DATES_KNOWN ) && $startDate >= $twoWeeksAgo && $startDate <= $twoWeeksLater) {
            $state += Offering::STATE_RECENT;
        }

        // Check if its recently added
        $course = $offering->getCourse();
        if ( $course->getCreated() >= $fourWeeksAgo ) {
            $state += Offering::STATE_JUST_ANNOUNCED;
        }

        // Check if its self paced
        if($offering->getStatus() == Offering::COURSE_OPEN) {
            $state += Offering::STATE_SELF_PACED;
            return $state;
        }

        // Check if its finished
        if ($endDate != null && $endDate < $now) {
            $state += Offering::STATE_FINISHED;
            return $state;
        }

        // Check if its ongoing
        if ( $offering->getStatus() == Offering::START_DATES_KNOWN && $yesterday     > $startDate) {
            $state += Offering::STATE_IN_PROGRESS;
            return $state;
        }

        // If it has reached here it means its upcoming.
        $state += Offering::STATE_UPCOMING;

        return $state;
    }


    /**
     * Calculates the next session for a course
     * @param Course $course
     * @return Offering
     */
    public static function getNextSession(Course $course)
    {

        $offerings = $course->getOfferings();

        // Remove all the offerings that are not valid
        $offerings->filter(
            function($offering)
            {
                return $offering->getStatus() == Offering::COURSE_NA;
            }
        );

        if( $offerings->isEmpty() )
        {
            return null;
        }

        // Categorize offerings into finished, ongoing, selfpaced, upcoming
        // Initialize the state map
        $offeringStateMap = array();
        foreach(Offering::$stateMap as $key => $value)
        {
            $offeringStateMap[$key] = array();
        }

        // Build the state map;
        foreach($offerings as $offering)
        {
            $states = self::getStates($offering);
            foreach($states as $state)
            {
                $offeringStateMap[$state][] = $offering;
            }
        }

        // Now that the state map is build - traverse in the following order until the next session is found
        // upcoming, self paced, ongoing, finished

        // upcoming
        if( !empty($offeringStateMap['upcoming']) )
        {
            if( count($offeringStateMap['upcoming']) == 1 )
            {
                return array_pop( $offeringStateMap['upcoming'] );
            }

            // Multiple sessions. Pick the next earliest upcoming session
            $next = array_shift( $offeringStateMap['upcoming'] );

            foreach( $offeringStateMap['upcoming'] as $uo)
            {
                if($uo->getStartDate() < $next->getStartDate() )
                {
                    $next = $uo;
                }
            }

            return $next;
        }

        // selfpaced
        if(!empty($offeringStateMap['selfpaced']))
        {
            return array_pop( $offeringStateMap['selfpaced'] );
        }

        // Ongoing
        if(!empty($offeringStateMap['ongoing']))
        {
            return array_pop( $offeringStateMap['ongoing'] );
        }

        // finished
        if( !empty($offeringStateMap['past']) )
        {
            if( count($offeringStateMap['past']) == 1 )
            {
                return array_pop( $offeringStateMap['past'] );
            }

            // Multiple sessions. Pick the last finished session
            $last = array_shift( $offeringStateMap['past'] );

            foreach( $offeringStateMap['past'] as $uo)
            {
                if($uo->getStartDate() > $last->getStartDate() )
                {
                    $last = $uo;
                }
            }

            return $last;
        }
        // Error: Should not come here
        return null;
    }


    // Given a state returns an array of states.
    // i.e recent, upcoming, ongoing etc
    public static function getStates(Offering $offering)
    {
        $state = CourseUtility::calculateState($offering);
        return self::getStatesFromState( $state );
    }


    /**
     * Given an integer value of state returns
     * an array of states
     * @param $state integer
     * @return array
     */
    public static function getStatesFromState ($state)
    {
        $stateMap = array_flip(Offering::$stateMap);
        $states = array();
        $unit = $state % 10;
        if(array_key_exists($unit,$stateMap))
        {
            $states[] = $stateMap[$unit];
        }

        if( $unit == Offering::STATE_RECENT_AND_JUST_ANNOUNCED )
        {
            $states[] = $stateMap[Offering::STATE_RECENT];
            $states[] = $stateMap[Offering::STATE_JUST_ANNOUNCED];
        }

        if(array_key_exists($state - $unit,$stateMap))
        {
            $states[] = $stateMap[$state - $unit];
        }
        return $states;

    }

    public static function calculateBoost( $states, \DateTime $date )
    {
        if( in_array('selfpaced', $states) )
        {
            return 12;
        }

        // recent = +2 while recent + just announced = +3. Equalize them
        if( in_array('recent',$states) && in_array('recentlyAdded',$states) )
        {
            return 4;
        }

        if( in_array('recent',$states) )
        {
            return 5;
        }

        if ( in_array( 'upcoming', $states) )
        {
            $dt = new \DateTime();
            $dt->add(new \DateInterval('P30D'));

            if( $date < $dt)
            {
                return 3;
            }

        }

        return 0;
    }

    /**
     * Returns an image url that can be used as the opengraph image tag
     * @param Course $c
     */
    public static function getImageUrl(Course $c)
    {
        $imageUrl = null;

        // Do institutions first
        foreach($c->getInstitutions() as $ins)
        {
            if( $ins->getImageUrl() )
            {
                $imageUrl = $ins->getImageDir() . '/' . $ins->getImageUrl();
                return $imageUrl;
            }
        }

        // Provider
        $provider = $c->getInitiative();
        if( $provider && $provider->getImageUrl() )
        {
            $imageUrl = $provider->getImageDir() . '/' . $provider->getImageUrl();
        }



        return $imageUrl;
    }
} 