<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 4/29/16
 * Time: 1:00 PM
 */

namespace ClassCentral\SiteBundle\Services;

use Guzzle\Http\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Pull in posts from MOOC Report
 * Class MOOCReport
 * @package ClassCentral\SiteBundle\Services
 */
class MOOCReport
{
    private $container;
    public static $baseUrl = 'https://www.class-central.com/report';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPosts($pageNo = 1)
    {
        $cache = $this->container->get('cache');

        return $cache->get('wp_new_posts_' . $pageNo,function($pageNo){
            try
            {
                $client = new Client();
                $request = $client->createRequest('GET', self::$baseUrl . '/wp-json/wp/v2/posts?per_page=20&page='.$pageNo);
                $response = $request->send();

                if($response->getStatusCode() !== 200)
                {
                    return array();
                }

                return json_decode($response->getBody(true),true);
            } catch (\Exception $e) {}
            return array();
        },array($pageNo));
    }


    public function getOpEds()
    {
        $cache = $this->container->get('cache');

        return $cache->get('wp_op_ed_posts',function(){
            try
            {

                $client = new Client();
                $request = $client->createRequest('GET', self::$baseUrl . '/wp-json/wp/v2/posts/?categories=26');
                $response = $request->send();

                if($response->getStatusCode() !== 200)
                {
                    return array();
                }

                return json_decode($response->getBody(true),true);
            } catch (\Exception $e) {}
            return array();
        });
    }

    public function getAuthor($authorId)
    {
        $cache = $this->container->get('cache');
        return $cache->get('wp_post_author_'.$authorId, function($authorId){
            try {
                $client = new Client();
                $request = $client->createRequest('GET', self::$baseUrl . '/wp-json/wp/v2/users/'. $authorId);
                $response = $request->send();

                if($response->getStatusCode() !== 200)
                {
                    return array('name' => '');
                }

                return json_decode($response->getBody(true),true);
            } catch (\Exception $e)
            {

            }
            return array('name' => '');
        }, array($authorId));

    }

}