<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 5:13 PM
 */

namespace ClassCentral\SiteBundle\Swiftype;

/**
 * Creates/Updates the swiftype index
 * Class SwiftypeIndexer
 * @package ClassCentral\SiteBundle\Swiftype
 */
class SwiftypeIndexer {

    private $token;
    private $engine;

    private $baseUrl = "https://api.swiftype.com/api/v1/engines/%s";
    private $bulkUrlFormat = '/document_types/%s/documents/%s';

    public function __construct($token, $engine)
    {
        $this->token = $token;
        $this->engine = $engine;
    }

    private function getBulkUrl($docType, $bulkCall)
    {
        return sprintf($this->baseUrl.$this->bulkUrlFormat, $this->engine,$docType, $bulkCall);
    }

    public function bulkCreateOrUpdate($docs, $documentType)
    {
        $url = $this->getBulkUrl($documentType,'bulk_create_or_update');
        $postObj = new \stdClass();
        $postObj->auth_token = $this->token;
        $postObj->documents = $docs;

        return $this->makeCurlPost($url,$postObj);
    }


    /**
     * Creates a particular doctype
     * @param $docType
     */
    public function createDocumentType($docType)
    {
        $url = sprintf($this->baseUrl . '/document_types.json',$this->engine);
        $postObj = new \stdClass();
        $postObj->auth_token = $this->token;
        $info = new \stdClass();
        $info->name = $docType;
        $postObj->document_type = $info;

        return $this->makeCurlPost($url,$postObj);
    }

    public function deleteDocumentType($docType)
    {
        $url = sprintf($this->baseUrl . '/document_types/%s?auth_token=%s',$this->engine,$docType,$this->token);
        return $this->makeCurlCall($url,'DELETE');
    }


    private function makeCurlPost($url, $postObj)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL,$url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postObj));
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-Type: application/json"));

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result,true);
    }

    /**
     * @param $url
     * @param $type GET or DELETE
     * @return mixed
     */
    private function makeCurlCall($url, $type = 'GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_URL,$url);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result,true);
    }




} 