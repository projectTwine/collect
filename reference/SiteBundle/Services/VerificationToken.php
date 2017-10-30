<?php

namespace ClassCentral\SiteBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;



class VerificationToken {

    private $em;

    public function __construct(Doctrine $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    /**
     * Create a token
     * @param $value Additional piece of information attached to each token
     * @param int $expiry Expiry in minutes. Default is 1 week
     * @return \ClassCentral\SiteBundle\Entity\VerificationToken
     * @throws \Exception
     */
    public function create($value, $expiry = \ClassCentral\SiteBundle\Entity\VerificationToken::EXPIRY_1_WEEK, $flush = true )
    {
        $expiry = intval($expiry);
        $vToken = new \ClassCentral\SiteBundle\Entity\VerificationToken();
        if($expiry > 0)
        {
            $vToken->setExpiry($expiry);
        }

        if(is_array($value))
        {
            $vToken->setTokenValueArray($value);
        }
        elseif(is_string($value))
        {
            $vToken->setValue($value);
        } else
        {
            throw new \Exception("VerificationToken: Token value should be an array or string");
        }

        $this->em->persist($vToken);
        if($flush)
        {
            $this->em->flush();
        }
        return $vToken;
    }

    /**
     * If the token string is valid is valid returns it
     * @param $token
     * @return null
     */
    public function get($token)
    {

        $verificationToken = $this->em->getRepository('ClassCentralSiteBundle:VerificationToken')->findOneByToken($token);
        if($verificationToken)
        {
            return $this->isValid($verificationToken) ? $verificationToken : null;
        }
        return null;
    }

    public function delete(\ClassCentral\SiteBundle\Entity\VerificationToken $vToken)
    {
        $this->em->remove($vToken);
        $this->em->flush();
    }

    /**
     * Checks for token expiry
     * @param \ClassCentral\SiteBundle\Entity\VerificationToken $vToken
     * @return bool
     */
    public function isValid(\ClassCentral\SiteBundle\Entity\VerificationToken $vToken)
    {
        $expiryDate = $vToken->getCreated();
        $expiry = $vToken->getExpiry();
        $date = new \DateTime();

        $expiryDate->add(new \DateInterval("PT" .$expiry ."M")); // Calcuate the expiry date
        return $expiryDate > $date;
    }

} 