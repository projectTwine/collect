<?php

namespace ClassCentral\SiteBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LanguageControllerTest extends WebTestCase {

    public function testLanguagePage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET','/language/arabic');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('h1[class=cc-page-header]')->count() > 0);
    }

    public function testLanguagesPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET','/languages');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        // Simple check to see if there are languages being displayed
        $this->assertTrue($crawler->filter('div[class=single-category]')->count() > 0);
    }
}