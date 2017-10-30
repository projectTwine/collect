<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dhawal
 * Date: 7/13/13
 * Time: 2:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace ClassCentral\SiteBundle\Services;

/**
 * Sends email via mailgun
 * Class MailgunService
 * @package ClassCentral\SiteBundle\Services
 */
class Mailgun {

    private $apiKey;
    private $apiUrl = 'https://api.mailgun.net/v2';
    private $mailDomain;
    private $sendEmail = false;

    const UTM_MEDIUM = 'email';
    const UTM_SOURCE_PRODUCT = 'product';

    private $mg;

    public function setApiKey($key)
    {
        $this->apiKey = $key;
    }

    public function setDomain($domain)
    {
        $this->mailDomain = $domain;
    }

    public function sendEmail ( $send )
    {
        $this->sendEmail = $send;
    }

    /*
     * Sends  a simple text email
     */
    public function sendSimpleText($to,$from, $subject, $text)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch,CURLOPT_USERPWD,"api:{$this->apiKey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, "https://api.mailgun.net/v2/{$this->mailDomain}/messages");

        curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => $from,
            'to' => $to,
            'subject' => $subject,
            'text' => $text,
            'o:tracking-clicks' => 'htmlonly'
            ));

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result,true);
    }

    public function sendIntroEmail($to, $from, $subject, $html,$userMetaData)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch,CURLOPT_USERPWD,"api:{$this->apiKey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, "https://api.mailgun.net/v2/{$this->mailDomain}/messages");

        curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => $from,
                'to' => $to,
                'subject' => $subject,
                'html' => $html,
                'o:tag' => 'welcome_email',
                'v:my-custom-data' => $userMetaData

        ));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * A wrapper around the Mailgun sendMessage call
     * @param array $params
     * @return mixed
     */
    public function sendMessage( $params = array() )
    {
        // Doesn't send email if testmode is true
        $params['o:testmode'] = !$this->sendEmail;
        return $this->getMG()->sendMessage(
            $this->mailDomain,
            $params
        );
    }

    private function getMG()
    {
        if (!$this->mg)
        {
            $this->mg = new \Mailgun\Mailgun( $this->apiKey );
        }

        return $this->mg;
    }

}