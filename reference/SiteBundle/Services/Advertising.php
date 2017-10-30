<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 6/4/16
 * Time: 4:11 PM
 */

namespace ClassCentral\SiteBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;

class Advertising
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Reads the text ads from the json file
     * @return array|mixed
     */
    public function getTextRowAds()
    {
        $filePath = $this->container->get('kernel')->getRootDir(). '/../extras/text_row_ads.json';
        $ads = file_get_contents($filePath);
        if($ads)
        {
            $ads = json_decode($ads,true);
            $freq = array();
            foreach($ads as $id => $ad)
            {
                $weight = $ad['frequency'];
                while($weight)
                {
                    $freq[] = $id;  //
                    $weight--;
                }
            }

            return array(
                'ads' => $ads,
                'freq' => $freq
            );
        }

        return array();
    }

    private function getTextAdBasedOnFrequency($textAds, $freq)
    {
        $len = count($freq);
        $id = mt_rand(0,$len - 1);

        return $textAds[$freq[$id]];
    }

    /**
     * Generates HTML rendering of the Text Row ad based on location and frequency specified in the json
     * @param $location
     */
    public function renderTextRowAd($location,$options = array())
    {
        $cache = $this->container->get('cache');
        $adsInfo = $cache->get('text_row_ads',array($this,'getTextRowAds'),array());
        $templating = $this->container->get('templating');

        $textAd = $this->getTextAdBasedOnFrequency($adsInfo['ads'],$adsInfo['freq']);
        $textAd['format'] = $location;
        $html = $templating->renderResponse(
            'ClassCentralSiteBundle:Helpers:table.ad.html.twig',
            array_merge($textAd,$options)
        )->getContent();

        return $html;
    }



}