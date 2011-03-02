<?php
namespace acl\lib;
use acl\models\User as User;
class CookieAuth
{
    private static $user;
    const COOKIE_NAME = 'identity';

    public static function hasAccessTo($smth)
    {
        $user = self::getUser();
        if($user)
        {
            return $user->hasResource($smth);
        }
        return false;
    }

    public static function mustHave($smth)
    {
        if(!self::hasAccessTo($smth))
        {
            self::doAuth();
        }
    }

    public static function login($user, $pwd, $expires = 0)
    {
        $user = User::findOne('login = ? and password = ?', array($user, sha1($pwd)));
        if(!$user)
        {
            return false;
        }
        self::setCookie($user->login, $expires);
        return true;
    }

    public static function logout()
    {
        setcookie(self::COOKIE_NAME, '', time() - 3600 * 24, '/', null, false, true);
        unset($_COOKIE[self::COOKIE_NAME]);
        self::$user = null;
    }

    public static function getUser()
    {
        if(empty($_COOKIE[self::COOKIE_NAME]))
        {
            return false;
        }
        if(empty(self::$user))
        {
            self::$user = User::findOne('login = ?', array(self::decrypt($_COOKIE[self::COOKIE_NAME])));
        }
        return self::$user;
    }

    public static function doAuth()
    {
        $s = \shozu\Shozu::getInstance();
        header('Location: ' . $s->url('acl/index/connect'));
        die;
    }

    public static function setCookie($user_login, $expires = 0)
    {
        setcookie(self::COOKIE_NAME, self::crypt($user_login), $expires, '/', null, false, true);
    }

    public static function crypt($text, $key = \SHZ_SALT)
    {
        return base64_encode(
                    mcrypt_encrypt(
                        MCRYPT_RIJNDAEL_256,
                        $key,
                        $text,
                        MCRYPT_MODE_ECB,
                        mcrypt_create_iv(
                            mcrypt_get_iv_size(
                                MCRYPT_RIJNDAEL_256,
                                MCRYPT_MODE_ECB
                            ),
                            MCRYPT_RAND
                        )
                    )
                );
    }

    public static function decrypt($text,$key = \SHZ_SALT)
    {
        return mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_256,
                    $key,
                    base64_decode($text),
                    MCRYPT_MODE_ECB,
                    mcrypt_create_iv(
                        mcrypt_get_iv_size(
                            MCRYPT_RIJNDAEL_256,
                            MCRYPT_MODE_ECB
                        ),
                        MCRYPT_RAND
                    )
                );
    }
}
