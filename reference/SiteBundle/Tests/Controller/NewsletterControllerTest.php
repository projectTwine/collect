<?php

namespace ClassCentral\SiteBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NewsletterControllerTest extends WebTestCase {

    private static $userControllerTest;

    public static function setUpBeforeClass() {
        self::$userControllerTest =  new UserControllerTest();
    }

    public function testSignupNewsletterForNewEmail()
    {
        $client = static::createClient();
        $email = sprintf("dhawal+%s@class-central.com",mt_rand());

        $crawler = $client->request('GET', '/newsletters/subscribe/mooc-report');
        //$crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /newsletters/subscribe/mooc-tracker");

        $newsletterForm = $crawler->selectButton('Subscribe')->form(array(
                'email' => $email
        ));
        $client->submit($newsletterForm);
        $crawler = $client->followRedirect();

        $this->assertGreaterThan(0, $crawler->filter("button:contains('Sign up')")->count());

        // Subscribed. Now it should ask for a password
        $mtSignupForm = $crawler->selectButton('Sign up')->form(array(
                'password' => 'Test1234',
                'name'     => 'John Smith'
        ));
        $client->submit($mtSignupForm);
        $crawler = $client->followRedirect();

        // The user should be signedup and logged in
        self::$userControllerTest->isSignedIn($crawler);

    }

    public function testSignupNewsletterForKnownEmail()
    {
        $client = static::createClient();
        $email = "dhawal@class-central.com";

        $crawler = $client->request('GET', '/newsletters/subscribe/mooc-report');
        //$crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /newsletters/subscribe/mooc-tracker");

        $newsletterForm = $crawler->selectButton('Subscribe')->form(array(
                'email' => $email
            ));
        $client->submit($newsletterForm);
        $crawler = $client->followRedirect();

        // No password form to create mooc tracker should be shown
        $this->assertEquals(0, $crawler->filter("button:contains('Signup for MOOC Tracker')")->count());
    }
}
 