<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/22/14
 * Time: 9:15 PM
 */

namespace ClassCentral\SiteBundle\Services;

use ClassCentral\SiteBundle\Entity\Course as CourseEntity;
use ClassCentral\SiteBundle\Entity\CourseStatus;
use ClassCentral\SiteBundle\Entity\Institution;
use ClassCentral\SiteBundle\Entity\ReviewSummary;
use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Utility\ReviewUtility;
use ClassCentral\SiteBundle\Entity\Review as ReviewEntity;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class Review {

    private $container;
    private $cache;
    private $em;

    const AVG_NUM_VOTES = 7 ;
    const AVG_RATING = 4 ;

    const REVIEW_ALREADY_SUMMARIZED_OR_EMPTY_TEXT = 0;
    const REVIEW_SUMMARY_FAILED = -1;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cache = $container->get('cache');
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * Calculate ratings for a particular course
     * @param Course $course
     */
    public function getRatings($courseId)
    {
        $ratingDetails = $this->cache->get(
            $this->getRatingsCacheKey($courseId),
            array($this,'calculateAverageRating'),
            array($courseId)
        );

        return $ratingDetails['rating'];
    }

    /**
     * Calculate ratings for a particular course
     * @param Course $course
     */
    public function getRatingsAndCount($courseId)
    {
        $ratingDetails = $this->cache->get(
            $this->getRatingsCacheKey($courseId),
            array($this,'calculateAverageRating'),
            array($courseId)
        );

        return $ratingDetails;
    }


    /**
     * Calculates the average rating
     * @param $courseId
     * @return array
     */
    public function calculateAverageRating($courseId)
    {
        $course = $this->em->getRepository('ClassCentralSiteBundle:Course')->findOneById($courseId);

        // Basic formula
        $rating = 0;
        $bayesian_average = 0;
        $reviews = $course->getReviews();
        $validReviewsCount = 0;
        if($reviews && $reviews->count() > 0)
        {
            $ratingSum = 0;
            foreach($reviews as $review)
            {
                if($review->getStatus() < ReviewEntity::REVIEW_NOT_SHOWN_STATUS_LOWER_BOUND)
                {
                    $ratingSum += $review->getRating();
                    $validReviewsCount++;
                }
            }

            if($validReviewsCount > 0)
            {
                $rating = $ratingSum/$validReviewsCount;
            }
        }
        return array(
            'rating' => $rating,
            'numRatings' => $validReviewsCount
        );
    }

    /**
     * Calculates the bayseian average rating. This is used for sorting
     * @param $courseId
     * @return float
     */
    public function getBayesianAverageRating( $courseId )
    {
        $ratingDetails = $this->cache->get(
            $this->getRatingsCacheKey($courseId),
            array($this,'calculateAverageRating'),
            array($courseId)
        );

        $bayesian_average = 0;
        $rating = $ratingDetails['rating'];
        $numRatings = $ratingDetails['numRatings'];

        if( $rating > 0 )
        {
            $bayesian_average = ((self::AVG_NUM_VOTES * self::AVG_RATING) + ($numRatings * $rating)) / (self::AVG_NUM_VOTES + $numRatings);
        }

        return round( $bayesian_average, 4);
    }

    public function getAverageRatingForInstitution(Institution $ins)
    {
        $numCourses = 0;
        $coursesWithRatings = 0;
        $numRatings = 0;
        $avgRatingSum = 0;
        $rating = 0;
        $totalRating = 0;
        foreach($ins->getCourses() as $course)
        {
            if($course->getStatus() == CourseStatus::AVAILABLE)
            {
                $numCourses++;
                $courseRatings = $this->calculateAverageRating($course->getId());
                if ( $courseRatings['rating'] > 0 )
                {
                    $numRatings += $courseRatings['numRatings'];
                    $totalRating+= $courseRatings['numRatings']*$courseRatings['rating'];
                    $avgRatingSum += $courseRatings['rating'];
                    $coursesWithRatings++;
                }
            }
        }
        if($numRatings > 0)
        {
            $rating = $totalRating/$numRatings;
        }

        return array(
            'rating' => $rating,
            'numRatings' => $numRatings,
            'numCourses' => $numCourses,
            'coursesWithRatings'=>$coursesWithRatings
        );
    }

    public function getReviews($courseId)
    {
        return $this->cache->get(
            $this->getReviewsCacheKey($courseId),
            array($this,'getReviewsArray'),
            array($courseId)
        );
    }

    public function getReviewsArray($courseId)
    {
        $course = $this->em->getRepository('ClassCentralSiteBundle:Course')->findOneById($courseId);

        $reviewEntities = $this->em->createQuery("
               SELECT r,f, LENGTH (r.review) as reviewLength from ClassCentralSiteBundle:Review r JOIN r.course c LEFT JOIN r.fbSummary f WHERE c.id = $courseId
                ORDER BY r.score DESC")
            ->getResult();
        $r = array();
        $reviewCount = 0;
        $ratingCount = 0;
        $ratingsBreakdown = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        );
        foreach($reviewEntities as $review)
        {
            $review = $review[0];
            if($review->getStatus() < ReviewEntity::REVIEW_NOT_SHOWN_STATUS_LOWER_BOUND )
            {
                $ratingCount++;
                $ratingsBreakdown[$review->getRating()]++;

                if( !$review->getIsRating() )
                {
                    // Hide the review table entries that are purely rating
                    $r[] = ReviewUtility::getReviewArray($review);
                    $reviewCount++;
                }
            }
        }

        $reviews = array();
        $reviews['count'] = $ratingCount;
        $reviews['ratingCount'] = $ratingCount;
        $reviews['reviewCount'] = $reviewCount;
        $reviews['ratingsBreakdown'] = $ratingsBreakdown;
        $reviews['reviews'] = $r;

        return $reviews;
    }

    public function getRatingsSummary($courseId)
    {

        $reviewEntities = $this->em->createQuery("
               SELECT r,f, LENGTH (r.review) as reviewLength from ClassCentralSiteBundle:Review r JOIN r.course c LEFT JOIN r.fbSummary f WHERE c.id = $courseId
                ORDER BY r.score DESC")
            ->getResult();

        $reviewCount = 0;
        $ratingCount = 0;
        $ratingsBreakdown = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        );
        foreach($reviewEntities as $review)
        {
            $review = $review[0];
            if($review->getStatus() < ReviewEntity::REVIEW_NOT_SHOWN_STATUS_LOWER_BOUND )
            {
                $ratingCount++;
                $ratingsBreakdown[$review->getRating()]++;

                if( !$review->getIsRating() )
                {
                    $reviewCount++;
                }
            }
        }

        $reviews = array();
        $reviews['count'] = $ratingCount;
        $reviews['ratingCount'] = $ratingCount;
        $reviews['reviewCount'] = $reviewCount;
        $reviews['ratingsBreakdown'] = $ratingsBreakdown;
        return $reviews;
    }

    /**
     * Calculate the percentage of ratings
     * @param $totalRatings
     * @param $numRatings
     */
    public function calculateRatingPercentage($totalRatings, $numRatings)
    {
        $percent = 0;
        if($totalRatings > 0)
        {
            $percent =intval($numRatings*100/$totalRatings);
            if ($percent < 10 & $numRatings > 0)
            {
                $percent = 10; // Do it so that number of reviews count is visible
            }
        }

        return $percent;
    }

    public function getReviewsCacheKey($courseId)
    {
        return "MOOC_REVIEWS_" . $courseId;
    }

    public function getRatingsCacheKey($courseId)
    {
        return "MOOC_RATINGS_" . $courseId;
    }

    public function getAverageRatingsCacheKey($courseId)
    {
        return "MOOC_AVERAGE_RATINGS_" . $courseId;
    }

    public function clearCache($courseId)
    {
        $this->cache->deleteCache($this->getReviewsCacheKey($courseId));
        $this->cache->deleteCache($this->getRatingsCacheKey($courseId));
    }

    /**
     * Creates/Updates a review
     * @param $courseId
     * @param $reviewData
     */
    public function saveReview($courseId, \ClassCentral\SiteBundle\Entity\User $user, $reviewData, $isAdmin = false)
    {
        $em = $this->em;
        $newReview = false;

        $course = $em->getRepository('ClassCentralSiteBundle:Course')->find($courseId);
        if (!$course) {
            return $this->getAjaxResponse(false,'Course not found');
        }

        // Get the review object if it exists
        $review = null;
        if(isset($reviewData['reviewId']) && is_numeric($reviewData['reviewId']))
        {
            // Its an edit. Get the review
            // Get the review
            $review = $em->getRepository('ClassCentralSiteBundle:Review')->find($reviewData['reviewId']);
            if(!$review)
            {
                return $this->getAjaxResponse(false, 'Review does not exist');
            }

            // Check if the user has access to edit the review
            // Either the user is an admin or the person who created the review
            $admin =  $this->container->get('security.context')->isGranted('ROLE_ADMIN');
            if(!$admin && $user->getId() != $review->getUser()->getId())
            {
                return $this->getAjaxResponse(false, 'User does not have access to edit the review');
            }

        } else
        {
            $newReview = true;
            $review = $em->getRepository('ClassCentralSiteBundle:Review')->findOneBy(array(
                'user' => $user,
                'course' => $course
            ));

            // Admins can create multiple reviews - for adding external reviews
            if($review && !$isAdmin)
            {
                return $this->getAjaxResponse(false, 'Review already exists');
            }

            $review = new \ClassCentral\SiteBundle\Entity\Review();
            $review->setUser($user);
            $review->setCourse($course);
        }

        // Get the offering
        if(isset($reviewData['offeringId']) && $reviewData['offeringId'] != -1)
        {
            $offering = $em->getRepository('ClassCentralSiteBundle:Offering')->find($reviewData['offeringId']);
            $review->setOffering($offering);
        }

        // check if the rating valid
        if(!isset($reviewData['rating']) &&  !is_numeric($reviewData['rating']))
        {
            return $this->getAjaxResponse(false,'Rating is required and expected to be a number');
        }
        // Check if the rating is in range
        if(!($reviewData['rating'] >= 1 && $reviewData['rating'] <= 5))
        {
            return $this->getAjaxResponse(false,'Rating should be between 1 to 5');
        }


        // If review exists its length should be atleast 20 words
        if(!empty($reviewData['reviewText']) && str_word_count($reviewData['reviewText']) < 20)
        {
            return $this->getAjaxResponse(false,'Review should be at least 20 words long');
        }

        $review->setRating($reviewData['rating']);
        $review->setReview($reviewData['reviewText']);


        // Progress is required
        if(!isset($reviewData['progress']))
        {
            return $this->getAjaxResponse(false,'Progress is required');
        }
        // Progress
        if(isset($reviewData['progress']) && array_key_exists($reviewData['progress'], UserCourse::$progress))
        {
            $review->setListId($reviewData['progress']);

            // Add/update the course to users library
            if(!$isAdmin)
            {
                // Do not add this t
                $userService = $this->container->get('user_service');
                $uc = $userService->addCourse($user, $course, $reviewData['progress']);
            }
        }

        // Difficulty
        if(isset($reviewData['difficulty']) && array_key_exists($reviewData['difficulty'], \ClassCentral\SiteBundle\Entity\Review::$difficulty))
        {
            $review->setDifficultyId($reviewData['difficulty']);
        }

        // Level
        if(isset($reviewData['level']) && array_key_exists($reviewData['level'], \ClassCentral\SiteBundle\Entity\Review::$levels))
        {
            $review->setLevelId($reviewData['level']);
        }

        // Effort
        if(isset($reviewData['effort']) && is_numeric($reviewData['effort']) && $reviewData['effort'] > 0)
        {
            $review->setHours($reviewData['effort']);
        }

        if($isAdmin)
        {
            // Status
            if(isset($reviewData['status']) && array_key_exists($reviewData['status'],\ClassCentral\SiteBundle\Entity\Review::$statuses))
            {
                $review->setStatus($reviewData['status']);
            }

            // External reviewer name
            if(isset($reviewData['externalReviewerName']) )
            {
                $review->setReviewerName($reviewData['externalReviewerName']);
            }

            // External review link
            if(isset($reviewData['externalReviewLink']) && filter_var($reviewData['externalReviewLink'], FILTER_VALIDATE_URL))
            {
                $review->setExternalLink( $reviewData['externalReviewLink'] );
            }

        }

        $user->addReview($review);
        $em->persist($review);
        $em->flush();

        $this->clearCache($courseId);

        // If its an actual user and not an anonymous user update the session information
        if($user->getEmail() != \ClassCentral\SiteBundle\Entity\User::REVIEW_USER_EMAIL)
        {
            //Update the users review history in session
            $userSession = $this->container->get('user_session');
            $userSession->saveUserInformationInSession();

            $showNotification = true;
            if( isset($reviewData['showNotification']) )
            {
                $showNotification = $reviewData['showNotification'];
            }
            if ($showNotification)
            {
                if($newReview)
                {
                    $userSession->notifyUser(
                        UserSession::FLASH_TYPE_SUCCESS,
                        'Review created',
                        sprintf("Review for <i>%s</i> created successfully", $review->getCourse()->getName())
                    );
                }
                else
                {
                    $userSession->notifyUser(
                        UserSession::FLASH_TYPE_SUCCESS,
                        'Review updated',
                        sprintf("Your review for <i>%s</i> has been updated successfully", $review->getCourse()->getName())
                    );
                }
            }

        }

        // Send a message in Slack
        if($newReview)
        {
            $message = ReviewUtility::getRatingStars($review->getRating()) .
                "\nReview {$review->getId()} created for Course *" . $review->getCourse()->getName(). "*".
                "\n *{$review->getUser()->getDisplayName()}*" . ReviewUtility::getReviewTitle( $review );
            ;

            if($review->getReview())
            {
                $message .= "\n\n" . $review->getReview();
            }

            $message .=  "\n" .  $this->container->getParameter('baseurl'). $this->container->get('router')->generate('review_edit', array('reviewId' => $review->getId() ));

            $message = str_replace('<strong>','_', $message);
            $message = str_replace('</strong>','_', $message);
            $this->container
                ->get('slack_client')
                ->to('#cc-activity-user')
                ->from( $review->getUser()->getDisplayName() )
                ->withIcon( $this->container->get('user_service')->getProfilePic( $review->getUser()->getId() ) )
                ->send($message);
        }
        return $review;
    }

    private function getAjaxResponse($success = false, $message = '')
    {
        $response = array('success' => $success, 'message' => $message);
        return json_encode($response);
    }

    /**
     * Wrapper around static function so that it can be accessed from twig
     * @param $rating
     * @return string
     */
    public function getRatingStars($rating)
    {
        return ReviewUtility::getRatingStars($rating);
    }

    /**
     * Wrapper around static function so that it can be accessed from twig
     * @param $rating
     * @return float
     */
    public function formatRating($rating)
    {
        return ReviewUtility::formatRating($rating);
    }

    /**
     * Summarizes the review and saves it in the database.
     * @param ReviewEntity $review
     * @return int returns the number of summaries for this particular review.
     */
    public function summarizeReview (\ClassCentral\SiteBundle\Entity\Review $review)
    {
        // Check whether if the review can be summarized
        if( !$this->isReviewSummarizable( $review ) )
        {
          return self::REVIEW_ALREADY_SUMMARIZED_OR_EMPTY_TEXT;
        }

        $summarizer = $this->container->get('text_summarizer');
        $response = $summarizer->summarize( $review->getReview() );
        if( empty($response) )
        {
            return self::REVIEW_SUMMARY_FAILED;
        }

        // Save the first one as default summary for the review
        $summaries = array();
        foreach( $response as $summary)
        {
            if( strlen($summary) < 20 )
            {
                // Skip smaller summaries
                continue;
            }
            $rs = $this->getReviewSummaryObj( $review, $summary);
            $this->em->persist( $rs );
            $summaries[] = $rs;
        }

        // Save the first summary as the default summary;
        if( !empty($summaries) )
        {
            $review->setReviewSummary( $summaries[0] );
            $this->em->persist( $review );
        }

        $this->em->flush();

        return count( $summaries );
    }

    /**
     * Summarizes reviews for all courses
     * @param Course $course
     * @return int
     */
    public function summarizeReviewsForACourse (CourseEntity $course)
    {
        $numSummarized = 0;
        foreach ($course->getReviews() as $review)
        {
            $response = $this->summarizeReview($review);
            if ($response != self::REVIEW_SUMMARY_FAILED && $response != self::REVIEW_ALREADY_SUMMARIZED_OR_EMPTY_TEXT)
            {
                $numSummarized++;
            }
        }

        return $numSummarized;
    }

    private function isReviewSummarizable(\ClassCentral\SiteBundle\Entity\Review $review)
    {
        // Is Review already summarized?
        if( $review->getReviewSummary() )
        {
            return false;
        }

        // Is there review text?
        if( strlen($review->getReview()) == 0 )
        {
            return false;
        }

        return true;
    }

    /**
     * Returns a ReviewSummary object
     * @param ReviewEntity $review
     * @param $summaryText
     * @return ReviewSummary
     */
    private function getReviewSummaryObj( \ClassCentral\SiteBundle\Entity\Review $review, $summaryText)
    {
        $rs = new ReviewSummary();
        $rs->setReview( $review);
        $rs->setSummary( trim($summaryText) );

        return $rs;
    }

    /**
     * Calculates a score for the review
     * @param ReviewEntity $review
     */
    public function scoreReview(\ClassCentral\SiteBundle\Entity\Review $review)
    {
        // Start with a base score
        $reviewScore = 100;

        /***********************************************
         * Update the score based on the yes/no votes
         * Subtract points if the review is deemed not helpful
         ***********************************************/
        $feedback = $this->getReviewFeedbackDetails( $review );
        if( $feedback['total']  > 0 )
        {
            $total = $feedback['total'];
            $positive = $feedback['positive'];
            $negative = $feedback['negative'];
            $helpful = $positive/$total;
            if($total > 5) {

                if($helpful >= 0.75)
                {
                    $reviewScore += 20; // Really helpful
                }

                if($helpful >= 0.5 && $helpful < 0.75)
                {
                    $reviewScore += 10; // Somewhat helpful
                }

                if($helpful >= 0.25 && $helpful < 0.5 )
                {
                    $reviewScore -= 5; // Somewhat not helpful
                }

                if($helpful < 0.25 )
                {
                    $reviewScore -= 10; // totally not helpful
                }

            } else {
                /**
                 * If the difference in total and positive votes then thre review is probably
                 * not helpful
                 */
                if( ($total - $positive) >= 3)
                {
                    // Probably not helpful review
                    $reviewScore -= 10;
                }
                else
                {
                    // Probably a helpful review
                    $reviewScore += 10;
                }

                $reviewScore += $positive/10;
            }

            // Boost scores based on total votes irrespective of negative/positive
            $reviewScore += $total/3;
        }

        /***********************************************
         * Update the score based on the length of the review
         ***********************************************/
        $reviewText = $review->getReview();
        if( !empty($reviewText) )
        {
            $reviewScore += 10; // Base boost

            // Boost based on the length of review
            $reviewScore += strlen($reviewText)/100;
        }

        return $reviewScore*10; // to convert from floating point to interger
    }

    public function getReviewFeedbackDetails(\ClassCentral\SiteBundle\Entity\Review $review)
    {
        $rsm = new ResultSetMapping();
        $em = $this->container->get('doctrine')->getManager();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('positive','positive');
        $rsm->addScalarResult('negative','negative');
        $rsm->addScalarResult('total','total');

        $q = $em->createNativeQuery(
            "SELECT
                SUM(if(helpful =0,1,0)) as negative,
                SUM(if (helpful =1,1,0)) as positive,
                count(*) as total FROM reviews_feedback
                WHERE review_id = " . $review->getId(), $rsm
        );
        $result = $q->getResult();

        return $result[0];
    }

    public function getReviewsSchema($reviews)
    {
        $r = array();

        foreach($reviews as $review)
        {

            $reviewer = $review['user']['name'];
            if( !empty($review['externalReviewerName']) )
            {
                $reviewer = $review['externalReviewerName'];
            }

            $r[] = array(
                '@type' => 'review',
                'datePublished' => $review['publishedDate'],
                'reviewBody' => $review['reviewText'],
                'author' => array(
                    '@type' => 'Person',
                    'name' => $reviewer
                ),
                'reviewRating' => array(
                    '@type' => 'Rating',
                    'ratingValue' => $review['rating']
                )
            );
        }

        return $r;
    }
} 