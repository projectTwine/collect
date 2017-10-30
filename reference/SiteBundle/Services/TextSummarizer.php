<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 1/2/15
 * Time: 9:56 PM
 */

namespace ClassCentral\SiteBundle\Services;


class TextSummarizer {

    private $apiKey;
    const PYTEASER_URL = 'https://textanalysis-text-summarization.p.mashape.com/text-summarizer-text';

    public function  __construct( $apiKey )
    {
        $this->apiKey = $apiKey;
    }

    /*
    For some reason this Unirest doesnt work - you get an HTTP 400 Bad Request
    public function summarize( $text )
    {

        \Unirest::verifyPeer(false);
        $response = \Unirest::post("https://textanalysis.p.mashape.com/pyteaser-text-summarizer",
            array(
                "X-Mashape-Key" => $this->apiKey,
                "Content-Type" => "application/x-www-form-urlencoded"
            ),
            array(
                "url" => "",
                "text" => $text
            )
        );

        return $response;
    }

    */

    /**
     * Calls the MASHAPE TextTeaser API using CURL.
     * @param $text
     * @return false if error or empty text. array of sentences if it executes perfectly
     */
    public function summarize($text)
    {
        if( empty($text) )
        {
            return false;
        }

        $fields = array(
            "text" => $text,
            "sentnum" => 5
        );


        //url-ify the data for the POST
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, self::PYTEASER_URL);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array(
            "X-Mashape-Key:{$this->apiKey}",
            "Content-Type : application/x-www-form-urlencoded"
        ));

        //execute post
        $content = curl_exec($ch);

        if (curl_error ( $ch ) )
        {
            return false;
        }

        //close connection
        curl_close($ch);

        $response = json_decode($content, true);

        return $response['sentences'];

    }
} 