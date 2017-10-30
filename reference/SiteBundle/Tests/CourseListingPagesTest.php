<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 4/13/14
 * Time: 3:57 PM
 */

namespace ClassCentral\SiteBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Tests different course listing pages like
 * - subjects
 * - institutions
 * - search
 * - languages
 * - provider
 * Class CourseListingPagesTest
 * @package ClassCentral\SiteBundle\Tests
 */
class CourseListingPagesTest extends WebTestCase {

    public function testPages()
    {
        $client = static::createClient();

        // Homepage
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('table[id=statustablelist] tr')->count() > 0);

        $crawler = $client->request('GET', '/courses/past');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('table[id=statustablelist] tr')->count() > 0);

        $crawler = $client->request('GET', '/provider/udacity');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('table[id=providertablelist] tr')->count() > 0);

        $crawler = $client->request('GET', '/university/stanford');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('table[id=institutiontablelist] tr')->count() > 0);

        $crawler = $client->request('GET', '/language/french');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('table[id=languagetablelist] tr')->count() > 0);

        $crawler = $client->request('GET', '/subject/cs');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('table[id=subjectstablelist] tr')->count() > 0);

        $crawler = $client->request('GET', '/search?q=music');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->assertTrue($crawler->filter('table[id=searchtablelist] tr')->count() > 0);
    }
} 