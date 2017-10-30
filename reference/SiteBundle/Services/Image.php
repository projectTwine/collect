<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 4/4/15
 * Time: 6:56 PM
 */

namespace ClassCentral\SiteBundle\Services;
use Imgix\UrlBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Image Manipulation library
 * @package ClassCentral\SiteBundle\Services
 */
class Image {

    private $container;
    private $kuber;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
        $this->kuber = $container->get('kuber');
    }

    /**
     * @param $imageUrl src image url
     * @param $height height of the cropped image
     * @param $width  width of the cropped image
     * @param $kuberEntity Course, Credential, User etc.
     * @param $kuberImageType type of image
     * @param $kuberEntityId  course_id, user_id
     * @param null $extension  File extension for the image
     * @return mixed
     */
    public function cropAndSaveImage($imageUrl, $height, $width, $kuberEntity, $kuberImageType, $kuberEntityId, $extension = null)
    {
        $uniqueKey = $kuberImageType . "_{$height}x{$width}_" . basename($imageUrl);
        if( $this->kuber->hasFileChanged( $kuberEntity, $kuberImageType, $kuberEntityId ,$uniqueKey ) )
        {
            // Upload the hew file
            $croppedImageUrl = $this->cropImage( $imageUrl, $height, $width );

            // Upload the file
            $filePath = '/tmp/modified_'.$uniqueKey;
            file_put_contents($filePath,file_get_contents($croppedImageUrl));

            $file = $this->kuber->upload(
                $filePath,
                $kuberEntity,
                $kuberImageType,
                $kuberEntityId,
                $extension,
                $uniqueKey
            );

            return $this->kuber->getUrlFromFile( $file );
        }

        // File exists
        return $this->kuber->getUrl(
            $kuberEntity,
            $kuberImageType,
            $kuberEntityId
        );
    }

    // Given an image its returns the image in spotlight sized
    public function getSpotlightImage($imageURl, $spotlightId)
    {
        return $this->cropImage($imageURl,160,198);

    }

    public function getInterviewImage($imageUrl, $interviewId)
    {
        $uniqueKey = 'interview_'. basename( $imageUrl );

        // Check if the file exists or has changed.
        if( $this->kuber->hasFileChanged( Kuber::KUBER_ENTITY_INTERVIEW,Kuber::KUBER_TYPE_COURSE_INTERVIEW_IMAGE, $interviewId ,$uniqueKey ) )
        {
            // Upload the hew file
            $croppedImageUrl = $this->cropImage( $imageUrl, 400, 400 );

            // Upload the file
            $filePath = '/tmp/modified_'.$uniqueKey;
            file_put_contents($filePath,file_get_contents($croppedImageUrl));

            $file = $this->kuber->upload(
                $filePath,
                Kuber::KUBER_ENTITY_INTERVIEW,
                Kuber::KUBER_TYPE_COURSE_INTERVIEW_IMAGE,
                $interviewId,
                null,
                $uniqueKey
            );

            return $this->kuber->getUrlFromFile( $file );
        }

        // File exists
        return $this->kuber->getUrl(
            Kuber::KUBER_ENTITY_INTERVIEW,
            Kuber::KUBER_TYPE_COURSE_INTERVIEW_IMAGE,
            $interviewId
        );
    }

    public function getCourseImageAd($imageUrl, $courseId)
    {
        $uniqueKey = 'course_image_ad_'. basename( $imageUrl );

        // Check if the file exists or has changed.
        if( $this->kuber->hasFileChanged( Kuber::KUBER_ENTITY_COURSE,Kuber::KUBER_TYPE_COURSE_IMAGE_AD, $courseId ,$uniqueKey ) )
        {
            // Upload the hew file
            $croppedImageUrl = $this->cropImage( $imageUrl, 100, 130 );

            // Upload the file
            $filePath = '/tmp/modified_'.$uniqueKey;
            file_put_contents($filePath,file_get_contents($croppedImageUrl));

            $file = $this->kuber->upload(
                $filePath,
                Kuber::KUBER_ENTITY_COURSE,
                Kuber::KUBER_TYPE_COURSE_IMAGE_AD,
                $courseId,
                null,
                $uniqueKey
            );

            return $this->kuber->getUrlFromFile( $file );
        }

        // File exists
        return $this->kuber->getUrl(
            Kuber::KUBER_ENTITY_COURSE,
            Kuber::KUBER_TYPE_COURSE_IMAGE_AD,
            $courseId
        );
    }

    public function getProfilePicThumbnail($imageUrl)
    {
        $cache = $this->container->get('cache');
        $uniqueKey = 'profile_pic_thumbnail_'. basename( $imageUrl );

        return $cache->get($uniqueKey,function($imageUrl){
            return $this->cropImage($imageUrl,50,50);
        },array($imageUrl));
    }

    public function getPostThumbnailSmall($imageUrl)
    {
        $cache = $this->container->get('cache');
        $uniqueKey = 'post_thumbnail_small'. basename( $imageUrl );

        return $cache->get($uniqueKey,function($imageUrl){

           return $this->cropImage($imageUrl,170,112);

        },array($imageUrl));
    }

    public function getPostThumbnailBig($imageUrl)
    {
        $cache = $this->container->get('cache');
        $uniqueKey = 'post_thumbnail_small'. basename( $imageUrl );

        return $cache->get($uniqueKey,function($imageUrl){

            return $this->cropImage($imageUrl,405,307);

        },array($imageUrl));
    }

    /**
     * Crops the image to a particular size
     * @param $imageUrl
     * @param $height
     * @param $width
     * @return string
     */
    public function cropImage($imageUrl, $height, $width)
    {
        // If Imgix params are missing, return the same image. Useful for dev environment.
        if(empty($this->getImgixKey()) || empty($this->getImgixToken()))
        {
            return $imageUrl;
        }

        $builder = new UrlBuilder($this->getImgixKey());
        $builder->setSignKey($this->getImgixToken());
        $builder->setUseHttps(true);
        $params = array(
            "w" => $width, "h" => $height, "auto" => 'compress'
        );

        return $builder->createURL($imageUrl,$params);
    }

    private function getImgixKey()
    {
        return $this->container->getParameter('imgix_domain');
    }

    private function getImgixToken()
    {
        return $this->container->getParameter('imgix_token');
    }
} 