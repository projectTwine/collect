<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/27/14
 * Time: 8:36 PM
 */

namespace ClassCentral\SiteBundle\Utility;


use ClassCentral\SiteBundle\Entity\User;

class CryptUtility {

    /**
     * Encrypts a string
     * @param $string
     * @param $key
     * @return string
     */
    public static function encrypt($string , $key)
    {
            return self::base64url_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
    }

    /**
     * Decrypts a string
     * @param $encrypted
     * @param $key
     * @return string
     */
    public static function decrypt($encrypted, $key)
    {
           return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), self::base64url_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
    }

    public  static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public  static function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public static function getUnsubscribeToken(User $user,$preference, $key)
    {
        $dt = new \DateTime();
        $str = sprintf("userPreference::%d::%d::%d",
            $user->getId(),
            $preference,
            $dt->getTimestamp()
        );

        return self::encrypt($str, $key);
    }

    public static function decryptUnsubscibeToken($token, $key)
    {
        $decrypted = self::decrypt( $token, $key);
        $details = explode('::', $decrypted);

        if( count($details) != 4 || $details[0] != 'userPreference')
        {
            throw new \Exception("Invalid token");
        }

        return array(
            'userId' => $details[1],
            'prefId' => $details[2]
        );
    }
} 