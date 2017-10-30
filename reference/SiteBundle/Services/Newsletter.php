<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 11/10/13
 * Time: 10:25 AM
 */

namespace ClassCentral\SiteBundle\Services;

use ClassCentral\SiteBundle\Entity\Email;
use Mailgun\Mailgun as MG;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interacts with mailgun
 * @package ClassCentral\SiteBundle\Services
 */
class Newsletter {

    private $mailDomain;
    private $mailgun;
    private $container;

    public function __construct($key, $domain, ContainerInterface $container)
    {
        $this->mailDomain = $domain;
        $this->mailgun = new MG($key);
        $this->container = $container;
    }

    public function subscribeUser(\ClassCentral\SiteBundle\Entity\Newsletter $newsLetter, \ClassCentral\SiteBundle\Entity\User $user)
    {
        return $this->subscribe($newsLetter->getCode(), $user->getEmail());
    }

    public function subscribeEmail(\ClassCentral\SiteBundle\Entity\Newsletter $newsLetter, Email $email)
    {
        return $this->subscribe($newsLetter->getCode(), $email->getEmail());
    }

    public function unSubscribeUser(\ClassCentral\SiteBundle\Entity\Newsletter $newsLetter, \ClassCentral\SiteBundle\Entity\User $user)
    {
        return $this->unsubscribe($newsLetter->getCode(), $user->getEmail());
    }

    public function unSubscribeEmail(\ClassCentral\SiteBundle\Entity\Newsletter $newsLetter, Email $email)
    {
        return $this->unSubscribe($newsLetter->getCode(), $email->getEmail());
    }

    /**
     * Sends an upsert subscribe request to mailgun
     * @param $newsLetterName
     * @param $email
     * @return boolean
     */
    public function subscribe($newsLetterName, $email)
    {
        $listAddress = $this->getListAddress($newsLetterName);
        try
        {
            $result = $this->mailgun->post("lists/$listAddress/members",
                array(
                    'address' => $email,
                    'subscribed' => true,
                    'upsert' => true
                )
            );
            return $result->http_response_code == 200;
        } catch (\Exception $e)
        {
            // Log the error
            return false;
        }
    }

    public function unSubscribe($newsLetterName, $email)
    {
        $listAddress = $this->getListAddress($newsLetterName);
        try
        {
            $result = $this->mailgun->delete("lists/$listAddress/members/". urlencode($email));
            return $result->http_response_code == 200;
        } catch (\Exception $e)
        {
            // Log the error
            return false;
        }
    }

    /**
     * If date is specified, the it sent at that time
     * @param \ClassCentral\SiteBundle\Entity\Newsletter $newsLetter
     * @param $html
     * @param $date
     */
    public function sendNewsletter(\ClassCentral\SiteBundle\Entity\Newsletter $newsLetter, $html,  $subject, \DateTime $date = null,$deliveryTime)
    {
        $listAddress = $this->getListAddress($newsLetter->getCode());

        $env = $this->container->getParameter('kernel.environment');

        // Avoid sending a bulk mail from the dev environment
        if($env !== 'prod')
        {
            $listAddress = $this->container->getParameter('test_email');
        }

        try {
            $params = array(
                'from' => "Class Central's MOOC Report <newsletter@". $this->mailDomain . '>',
                'to' => $listAddress,
                'subject' =>$subject,
                'html' => $html,
                'o:tag' => $newsLetter->getCode(),
                'o:deliverytime' => $deliveryTime
            );
            if($date)
            {
                // TODO: Add date to params
            }

            $result = $this->mailgun->sendMessage($this->mailDomain,$params);
            return $result->http_response_code == 200;
        }
        catch (\Exception $e)
        {
            // Log the error
            return false;
        }
    }

    protected  function getListAddress($newsLetterName)
    {
        return sprintf("%s@%s",$newsLetterName,$this->mailDomain);
    }
} 