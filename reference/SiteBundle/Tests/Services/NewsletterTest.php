<?php

require_once dirname(__DIR__).'/../../../../app/AppKernel.php';


class NewsletterTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    private $container;

    /**
     * Initialize a kernel to retrieve values
     */
    public function setUp()
    {
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();

        parent::setUp();
    }

    /**
     * Expects the mailing list "mooc-tracker@rs8422.mailgun.org" to already exist
     */
    public function testUserIsSubscribed()
    {
        $newsletterService = $this->getNewsletterService();
        $newsletter = $this->getNewsletter();

        $email = sprintf("testUser+%s@class-central.com",time());
        $user = new \ClassCentral\SiteBundle\Entity\User();
        $user->setEmail($email);

        $result = $newsletterService->subscribeUser($newsletter, $user);
        $this->assertTrue($result,"Failed to subscribe user to a newsletter " . $newsletter->getCode());

        // Unsubscribe this user
        $unsubscribe = $newsletterService->unSubscribeUser($newsletter, $user);
        $this->assertTrue($unsubscribe,"Failed to unsubscribe user from a newsletter" . $newsletter->getCode());
    }

    public function testEmailIsSubscribed()
    {
        $newsletterService = $this->getNewsletterService();
        $newsletter = $this->getNewsletter();

        $email = sprintf("testUser+%s@class-central.com",time());
        $emailEntity = new \ClassCentral\SiteBundle\Entity\Email();
        $emailEntity->setEmail($email);

        $result = $newsletterService->subscribeEmail($newsletter, $emailEntity);
        $this->assertTrue($result,"Failed to subscribe user to a newsletter " . $newsletter->getCode());

        // Unsubscribe this user
        $unsubscribe = $newsletterService->unSubscribeEmail($newsletter, $emailEntity);
        $this->assertTrue($unsubscribe,"Failed to unsubscribe user from a newsletter" . $newsletter->getCode());
    }



    private function getNewsletterService()
    {
        $key = $this->container->getParameter("test_mailgun_api_key");
        $domain = $this->container->getParameter("test_mailgun_domain_name");

        return new \ClassCentral\SiteBundle\Services\Newsletter($key,$domain);
    }

    private function getNewsletter()
    {
        $newsletter = new \ClassCentral\SiteBundle\Entity\Newsletter();
        $newsletter->setCode("mooc-report");

        return $newsletter;
    }
}