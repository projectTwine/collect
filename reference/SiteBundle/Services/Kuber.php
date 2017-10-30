<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 8/20/14
 * Time: 5:58 PM
 */

namespace ClassCentral\SiteBundle\Services;
use Aws\S3\S3Client;
use ClassCentral\SiteBundle\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This is the file management API
 * Class Kuber
 * @package ClassCentral\SiteBundle\Services
 */
class Kuber {

    private $container;

    private  $s3Client;
    private  $awsAccessKey;
    private  $awsAccessSecret;
    private  $s3Bucket;
    private  $baseUrl;

    const KUBER_ENTITY_USER = 'users';
    const KUBER_ENTITY_COURSE = 'courses';
    const KUBER_ENTITY_SPOTLIGHT = 'spotlights'; // Images folder used for cropped and resized images from embedly
    const KUBER_ENTITY_INTERVIEW = 'interviews';
    const KUBER_ENTITY_CREDENTIAL = 'credentials';
    const KUBER_ENTITY_POST = 'posts';


    const KUBER_TYPE_USER_PROFILE_PIC = "profile_pic";
    const KUBER_TYPE_USER_PROFILE_PIC_TMP = "profile_pic_tmp";
    const KUBER_TYPE_COURSE_IMAGE = 'course_image';
    const KUBER_TYPE_COURSE_INTERVIEW_IMAGE = 'course_interview_image';
    const KUBER_TYPE_COURSE_IMAGE_AD = 'course_image_ad';
    const KUBER_TYPE_SPOTLIGHT_IMAGE = 'image';
    const KUBER_TYPE_CREDENTIAL_IMAGE = 'credential_image';
    const KUBER_TYPE_CREDENTIAL_CARD_IMAGE = 'credential_card_image';
    const KUBER_TYPE_POST_THUMBNAIL_SMALL = 'post_thumbnail_small';



    private function getS3Client()
    {
        if(!$this->s3Client)
        {
            $this->s3Client = S3Client::factory(array(
                'key' => $this->awsAccessKey,
                'secret' => $this->awsAccessSecret,
            ));
        }

        return $this->s3Client;
    }

    public function __construct(ContainerInterface $container, $aws_access_key, $aws_access_secret, $s3_bucket,$base_url)
    {
        $this->container = $container;
        $this->awsAccessKey = $aws_access_key;
        $this->awsAccessSecret = $aws_access_secret;
        $this->s3Bucket = $s3_bucket;
        $this->baseUrl = $base_url;
    }

    /**
     * Uploads the file to the Amazon s3.
     * Entity, type, and entityId are the unique keys
     * @param $filePath Path of the file
     * @param $entity type of entity i.e User
     * @param $type type of file related to the entity i.e Spotlight, Profile Pic etc
     * @param $entity_id i.e user_id, course_id
     */
    public function upload( $filePath, $entity, $type, $entity_id,$extension = null, $uniqueKey = null )
    {
        $client = $this->getS3Client();
        $em = $this->container->get('doctrine')->getManager();
        $logger = $this->getLogger();

        $name = $this->generateFileName( $filePath,$extension );
        // Check if the file already exists
        $file = $this->getFile( $entity,$type,$entity_id);
        if( $file )
        {
            // Delete the original file
            try
            {
                $result = $client->deleteObject(array(
                    'Bucket' => $this->s3Bucket,
                    'Key'    => $this->getKeyName( $file )
                ));
            } catch(\Exception $e)
            {
                $logger->error( "Error trying to delete file during upload " . $e->getMessage(),array(
                    'Entity' => $entity,
                    'Entity_Id'=> $entity_id,
                    'Type' => $type
                ));
            }

            // Update the file name
            $file->setFileName( $name );
            $file->setFileType( mime_content_type($filePath) );
        }
        else
        {
            $file =  new File();
            $file->setEntity( $entity );
            $file->setType( $type );
            $file->setEntityId( $entity_id );
            $file->setFileName( $name );
            $file->setFileType( mime_content_type($filePath) );
        }

        // Update the key
        $file->setUniqueKey( $uniqueKey );

        try
        {
            // Upload the file
            $result = $client->putObject(array(
                'Bucket' => $this->s3Bucket,
                'Key' => $this->getKeyName( $file),
                'SourceFile' => $filePath
            ));
            $logger->info( "File uploaded for Entity $entity with type $type and Entity Id $entity_id",  (array)$result);

            $em->persist($file);
            $em->flush();

            return $file;
        } catch ( \Exception $e) {
            // Log the exception
            $logger->error( "Exception occurred while uploading file - " . $e->getMessage(),array(
                'Entity' => $entity,
                'Entity_Id'=> $entity_id,
                'Type' => $type
            ));
            return false;
        }
    }

    /**
     * Deletes a file from S3 as well as the
     * database
     * @param File $file
     */
    public function delete(File $file)
    {
        $client = $this->getS3Client();
        $em = $this->container->get('doctrine')->getManager();
        $logger = $this->getLogger();

        // Remove the file from S3
        try
        {
            $client->deleteObject(array(
                'Bucket' => $this->s3Bucket,
                'Key' => $this->getKeyName( $file),
            ));
        } catch(\Exception $e) {
            // Log the exception
            $logger->error( "Exception occurred whle deleting file - " . $e->getMessage(),array(
                'Entity' => $file->getEntity(),
                'Entity_Id'=> $file->getEntityId(),
                'Type' => $file->getType()
            ));
        }

        // Delete the record in the files table
        $em->remove( $file);
        $em->flush();

    }

    /**
     * Returns a url for that particular file
     * @param $entity
     * @param $type
     * @param $entity_id
     */
    public function getUrl($entity,$type,$entity_id)
    {
        $file = $this->getFile($entity,$type,$entity_id);
        if($file)
        {
            return $this->getUrlFromFile($file);
        }

        // No file exists
        return null;
    }

    /**
     * Returns the url for particular file
     * @param File $file
     * @return string
     */
    public function getUrlFromFile(File $file)
    {
        $keyName = $this->getKeyName( $file );
        return $this->baseUrl . '/' . $keyName;
    }

    /**
     * Gets the file entity from the data base
     * @param $entity
     * @param $type
     * @param $entity_id
     * @return mixed
     */
    public function getFile($entity,$type,$entity_id)
    {
        $client = $this->getS3Client();
        $em = $this->container->get('doctrine')->getManager();

        return $em->getRepository('ClassCentralSiteBundle:File')->findOneBy(array(
            'entity' => $entity,
            'type'   => $type,
            'entityId' => $entity_id
        ));
    }

    /**
     *
     * @param $entity
     * @param $type
     * @param $entity_id
     * @param $uniqueKey
     */
    public function hasFileChanged( $entity,$type,$entity_id, $uniqueKey)
    {
        $file = $this->getFile( $entity, $type, $entity_id);
        if( $file )
        {
                return $file->getUniqueKey() != $uniqueKey;
        }

        return true;
    }

    /**
     * Generates a unique filename
     * @param $filePath
     * @return string
     */
    private function  generateFileName( $filePath,$extension )
    {
        if(!$extension)
        {
            $fileParts = pathinfo($filePath);

            if(isset($fileParts['extension']))
            {
                $extension = $fileParts['extension'];
            }

            // Get the extension from mimetype
            if(empty($extension))
            {
                $parts =  explode('/',mime_content_type($filePath));
                $extension = $parts[1];
            }
        }
        $time = microtime();
        return substr(md5( $this->generateRandomString() + $time ),0,12). '.'.$extension ;
    }

    /**
     * Builds a key name for S3.
     * @param File $file
     * @return string
     */
    private function getKeyName( File $file)
    {
        return strtolower( $file->getEntity() . '/' . $file->getType() . '/' . $file->getFileName() );
    }

    private function getLogger()
    {
        return $this->container->get('logger');
    }

    private function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
} 