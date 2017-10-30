<?php
namespace ClassCentral\SiteBundle\Command\Network;

use ClassCentral\SiteBundle\Command\Network\NetworkAbstractInterface;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Utility\ReviewUtility;

class RedditNetwork extends NetworkAbstractInterface
{
    public static $coursesByLevel = array(
        'beginner' => array(
            442,1586,1578,1325,1046,320,831,1580,303, 1341, 441, 4891, 408, 1891, 2013, 2013,
            306, 529, 1983, 1850,1957, 2042,2175,1650,732,1857,1727,
            1349, 2298, 1651,2195, 2486, 2659,2660, 2661, 2129, 2448, 1243,
            2630, 2954, 2957,2731,3196, 2809, 2938, 3253, 3338, 3234,
            3396, 2813, 3231, 3486, 3444, 3483, 3353, 3925,2525, 4239, 4256,
            4258, 4307, 4319, 4062, 4333, 4294, 2532, 3770, 4084, 2548, 3695, 4270, 3997,
            5765, 5815, 6531, 6686,4338, 335, 5420,390,385,7017,7027,4276, 7087,7212,7362,7363,7336, 5735,7199,
            7278, 7494, 7623, 7631, 7630,7315, 7849,6265,4049,5764,5923,7313,8059,7622,8054,8202,1797, 8003, 5652,8496,8527,7219,7211,359, 8577,5998,8542,8543,8374,3781,7379, 8671, 8718, 8770,
            8725, 8723, 8593, 8866, 8518, 8651,8650, 8808
        ),
        'intermediate' => array(
            824,599,616,1176,1470,1188,1585,1205,462,1178,339,1478,1479,1480,328,366,323,
            324,325,364,365,457,455,592, 551, 1299, 1701, 1523, 921, 846, 1457, 1742, 1282,
            650, 417, 594, 1187, 1737, 1738, 1487,849,475, 1021, 835, 428,1152,487,1779,1816,1209,526,340, 724, 764,588,
            1748, 374, 1777,1875,531,1238,422,1529, 443, 1206, 1377, 1848, 1865 , 1881,
            533, 489, 1906, 451, 453, 426, 600, 558, 1724,
            1704,2211,2212,
            2215, 2335,2336, 2067,1053,1725,2214, 1345, 376, 2340, 342,
            1766, 2406, 745,  1240, 2427, 2658, 2503, 452,  548, 429, 2147, 1728, 2144,
            2716, 2244, 1730, 2861, 2445, 2997, 2898,2998,2996, 627, 3082, 3082,
            2717, 2290, 3076, 3026, 1031, 2983, 445, 2291, 3075, 2964, 3198, 3077, 3273, 2734, 2942, 3230, 3254, 3255,
            3288, 3152, 2916, 1186, 3295, 3350, 2339, 3351, 3393, 3524, 3525, 3526, 3527, 3455, 3078,
            3536, 3459, 3472, 3465, 3343, 3341, 3339, 3535, 3079, 3579, 3580, 3581,3582,3584,
            1564, 1836,1837, 3200, 3758, 3476, 3478, 3928, 2738, 3642, 4013, 4071,
            4174,3466, 4191, 4248, 4268, 4337, 4164, 4196, 4212,4184,4419, 4240, 4305, 4343, 4348, 4169, 4197,
            4200, 4203, 4206, 4187, 4230, 3931, 3418, 4856, 4887, 4272, 4152, 2732, 4473, 4297, 4321, 4325, 4328, 4362,
            4810, 4292, 4302, 4251,4937,4949, 4295,3768,4671,5026,4288,4323,4334,4356,4175,4181,4224,4346, 5055,4265, 4190,
            2778,5287,3984, 5303, 5679,5680,4155,4805,4318,4925,5497,5502,5016,4812,4280,5471,4327,4229,3464, 5888,5592,5446,5704,5794,
            5633, 5683,5536, 4261,6143,3777,5475,5755,5422, 6000, 5537, 3080,5479, 5719,5451, 5387,5460, 6304,
            4283,6290,5423,5564,6512,6511,4990,6235,6038,6466, 5419,4269,6040,6357,6559,5753,
            6477,6493,6527,6548,6549, 6728, 6086,1193, 6085,1713,1712,1719,1715,1718, 5474,6467, 2737,
            3473,5513,4235,5470,6420,1714,1717,1716,4388,6523, 6507,6837,6796,6797,
            6798,6879,6878,6584,4260,6920,6842,6748,4173,6752,6636,6671,6469,5752,6607,6956, 6809,
            6809,4276, 7187,7170,3936,6585,6586,7202,6931,7174,6300,6808,3934,6528,6991,7092,671,3933,4389,4392,5151,
            7217, 7208,5744,7204,7415,7175,7384,7352,7354,5174,3820,5500,  6839,4182, 4391,5028,7279,7342,7343,
            7480,7463, 7495, 7506, 7807, 7293, 7476, 7350,7176, 7501,
            8022,7784,7660,7805,7763,7351,7745,7840,8025, 7751, 8056,7844,8093,7785,7837,891,7391,8071,8093,2189,4050,3996,5965,2976,6274,17911,6333,7753,7754,
            7755,7376,4480,5156,6406,6471,6405,8002,4758,8080,7242,7852,7346,8369,7454,2960, 7590, 6275,5388,7377,8389,8162,8217,7642,8179,8168,7857, 8516, 8118,6806,6141,8199,8517,8614, 4275, 4315, 7783,
            4518, 8573, 8568, 8633,8514, 8422,8394,7380, 8682, 8687, 6658, 8652, 8719, 8683, 8681, 8764, 8520, 7845, 8722, 8670,7405,
            8200,8171, 8820, 8937, 6475, 8839, 8823
        ),
        'advanced' => array(
            427,449,414,319,326,549, 552, 425, 595, 1729, 2733,1623, 3256,1016, 1018, 1024, 1025, 1020,
            2735, 1029, 3458, 2965, 2781, 2736, 3531, 3433, 3692, 3917, 3655, 3024,  4352, 3954,
            3332, 1023, 1022,4734, 3419, 1028, 4864, 4341, 416, 4351,1026, 4219,4199,4913,4238,4354, 4289, 5288,5290,4912,
            3420,5681,4249,5688,4313,4911,5425,6288, 5661, 5855,5960,5851,309,3475,3290,3289,6083,398,6927,4215,
            6825,6180,6889,5421,6604,5854,6944,6309, 6881,6826, 6918,3556,6334,7202,7025,7193,1720,6843,6672,
            6673,6674,6670,7420,6933,7292,6603,6693,7338,3557,3291,8123,7759,3474,7757, 3555, 8130,1847,1849,3000,1911,2973,6146,4804,8021,8083,8097,8132,
            8234,8133,7769,7808,8387,7887,7230, 642,7231,8480,8509,8157,8481,7046, 7803, 7786, 6679, 8565, 8569,8570,8572,8746
        )
    );

    // Skipped because of lack of characters in Reddit post
    public $skip = array(
        4269,4216,1701,2340,3931,1209,745,3341,5815,5719,1727,3584,4856,3339,5564,3478
    );

    public static function getCourseToLevelMap()
    {
        $map = array();
        foreach(self::$coursesByLevel as $level => $courses)
        {
            foreach($courses as $course)
            {
                $map[$course] = $level;
            }
        }
        return $map;
    }

    public function outInitiative( $name , $offeringCount)
    {
        $this->output->writeln( strtoupper($name) . "({$offeringCount})");
        $this->output->writeln('');
    }

    public function beforeOffering()
    {
        // Table header row
        $this->output->writeln("Course Name|Start Date|Length (in weeks)|Rating");
        $this->output->writeln(":--|:--:|:--:|:--:|:--:");
    }


    public function outOffering(Offering $offering)
    {
        $course = $offering->getCourse();
        $rs = $this->container->get('review');

        // Figure out whether the course is new
        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->sub(new \DateInterval("P30D"));
        $newCourse = false;
        if($course->getCreated() >= $oneMonthAgo)
        {
            $newCourse = true;
        }
        // Is it being offered for he first time
        if(count($course->getOfferings()) == 1 and $offering->getCreated() > $oneMonthAgo  )
        {
            $newCourse = true;
        }
        if(count($course->getOfferings()) == 1 and $offering->getStatus() != Offering::COURSE_OPEN )
        {
            $newCourse = true;
        }

        $courseUrl =$offering->getUrl();
        if($offering->getCourse()->getInitiative() && ($offering->getCourse()->getInitiative()->getName() == 'Udacity' || $offering->getCourse()->getInitiative()->getName() == 'FutureLearn') )
        {
            $courseUrl = strtok($courseUrl, '?');
        }


        $courseName = $offering->getCourse()->getName();


        if($offering->getInitiative() == null)
        {
            $initiative = 'Others';
        }
        else
        {
            $initiative = $offering->getInitiative()->getName();
            if($initiative == 'edX')
            {
                $doubleDot = strpos($courseName,':');
                $courseName = substr($courseName,$doubleDot+2);
            }
        }

        $name = '[' . $courseName. ']' . '(' .$courseUrl . ')';

        $startDate = $offering->getDisplayDate();
        
        $startDate = array_shift( explode(',',$startDate) ); // Do not show the year to save characters


        $length = 'NA';
        if(  $offering->getCourse()->getLength() != 0)
        {
            $length = $offering->getCourse()->getLength() ;
        }

        // Rating
        $courseRating = round($rs->getRatings($offering->getCourse()->getId()),1);
        $courseReviews = $rs->getReviewsArray( $offering->getCourse()->getId() );
        $reviewText = '';
        if($courseRating == 0)
        {
            $courseRating = 'NA';
        }
        else
        {
            $reviewText = sprintf("(%d)", $courseReviews['count']);
        }
        $url = 'https://www.class-central.com'. $this->router->generate('reviews_short_url', array('courseId' => $offering->getCourse()->getId() ));
        //$url .= '#reviews';
        // $ratingStars = ReviewUtility::getRatingStars($courseRating);
        $ratingStars = $courseRating . '★';
        if($courseRating > 0)
        {
            $rating = "[$ratingStars $reviewText]($url)";
        }
        else
        {
            $rating = "NA";
        }

        $new ='';
        if($newCourse)
        {
            $new = "[NEW]";
        }
        

        $this->output->writeln("{$new} $name via **$initiative**|$startDate|$length|$rating");
    }


}


