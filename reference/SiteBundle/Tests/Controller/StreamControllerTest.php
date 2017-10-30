<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/2/13
 * Time: 4:30 PM
 */

namespace ClassCentral\SiteBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StreamControllerTest extends WebTestCase {

    public function testSubjectsPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET','/subjects');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        // Simple check to see if there are subjecsts being displayed
        $this->assertTrue($crawler->filter('div[class=category-header]')->count() > 0);

    }
} 