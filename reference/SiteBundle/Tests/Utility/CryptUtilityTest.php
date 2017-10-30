<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/27/14
 * Time: 8:45 PM
 */

namespace ClassCentral\SiteBundle\Tests\Utility;


use ClassCentral\SiteBundle\Entity\User;
use ClassCentral\SiteBundle\Entity\UserPreference;
use ClassCentral\SiteBundle\Utility\CryptUtility;

class CryptUtilityTest extends \PHPUnit_Framework_TestCase {

    private $key = 'RandomlyGeneratedKey';

    public function testEncryptDecrypt()
    {
        $str = 'randomstring#12312321';

        $encrypted = CryptUtility::encrypt($str, $this->key);
        $decrypted = CryptUtility::decrypt($encrypted, $this->key);

        $this->assertEquals($str,$decrypted);
    }

    public function testUnsubscribeTokenEncryptDecrypt()
    {
        $uid = 26;
        $u = new User();
        $u->setId($uid);
        $token = CryptUtility::getUnsubscribeToken( $u, UserPreference::USER_PREFERENCE_MOOC_TRACKER_COURSES, $this->key);
        $details = CryptUtility::decryptUnsubscibeToken( $token, $this->key);

        $this->assertEquals( $uid, $details['userId']);
        $this->assertEquals( UserPreference::USER_PREFERENCE_MOOC_TRACKER_COURSES, $details['prefId'] );
    }
} 