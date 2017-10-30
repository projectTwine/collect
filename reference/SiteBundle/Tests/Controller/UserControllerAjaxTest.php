<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/24/13
 * Time: 4:54 PM
 */

namespace ClassCentral\SiteBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerAjaxTest extends WebTestCase{

    private static $email;
    private static $password ='Test1234';
    private $loggedInClient = null;

    public static function setUpBeforeClass()
    {
        self::$email = sprintf("dhawal+%s@class-central.com",mt_rand());
    }

    public function testCoursesAjaxCall()
    {
        // Signup a new user

        $client = static::createClient();
        $crawler = $client->request('GET', '/signup');
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /signup");

        // Fill the signup form
        $form = $crawler->selectButton('Sign Up')->form(array(
                'classcentral_sitebundle_signuptype[email]' => self::$email,
                'classcentral_sitebundle_signuptype[name]' => "Dhawal Shah",
                'classcentral_sitebundle_signuptype[password][password]' =>  self::$password,
                'classcentral_sitebundle_signuptype[password][confirm_password]' => self::$password
            ));

        $crawler = $client->submit($form);

        $crawler = $client->followRedirect();
        $this->isSignedIn($crawler);

        // Add a course for the signed in user
        $crawler = $client->request('GET','/ajax/user/course/add?c_id=1261&l_id=1');
        $response = json_decode($crawler->text(),true);
        $this->assertTrue($response['success'],"Course was not added to user");

        // Remove the course now
        $crawler = $client->request('GET','/ajax/user/course/remove?c_id=1261&l_id=1');
        $response = json_decode($crawler->text(),true);
        $this->assertTrue($response['success'],"Course could not be removed");

        // Remove should fail
        $crawler = $client->request('GET','/ajax/user/course/remove?c_id=1261&l_id=1');
        $response = json_decode($crawler->text(),true);
        $this->assertFalse($response['success'],"Course does not exist. Remove should fail");

        // Add the course again and it should work
        $crawler = $client->request('GET','/ajax/user/course/add?c_id=1261&l_id=1');
        $response = json_decode($crawler->text(),true);
        $this->assertTrue($response['success'],"Course was not added to user");


        // Newsletter subscription - unsubscribe
        $crawler = $client->request('GET', '/ajax/newsletter/subscribe/mooc-report');
        $response = json_decode($crawler->text(),true);
        $this->assertTrue($response['success'],"Newsletter was subscribed");

        // Newsletter subscription - unsubscribe
        $crawler = $client->request('GET', '/ajax/newsletter/unsubscribe/mooc-report');
        $response = json_decode($crawler->text(),true);
        $this->assertTrue($response['success'],"Newsletter was not subscribed");

        // User Preference - mooc tracker courses - unchecked
        $crawler = $client->request('GET', '/ajax/user/pref/100/0');
        $response = json_decode($crawler->text(),true);
        $this->assertTrue($response['success'],"User preference was not unchecked");

        // User Preference - mooc tracker courses - checked
        $crawler = $client->request('GET', '/ajax/user/pref/100/1');
        $response = json_decode($crawler->text(),true);
        $this->assertTrue($response['success'],"User preference was not checked");
    }

    public function isSignedIn($crawler)
    {
        $this->assertGreaterThan(0, $crawler->filter("a:contains('My Courses')")->count());
    }
} 