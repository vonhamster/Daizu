<?php
namespace acl\lib;
use acl\models\User as User;
class BasicAuth
{
    /**
     * Check if user has access to resource
     */
    public static function hasAccessTo($smth)
    {
        $u = self::getUser();
        if($u)
        {
            if($u->hasResource($smth))
            {
                return true;
            }
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

    public static function doAuth($realm = 'login')
    {
        header('WWW-Authenticate: Basic realm="' . $realm . '"');
        header('HTTP/1.0 401 Unauthorized');
        die($realm);
    }

    public static function getUserName()
    {
        $u = self::getUser();
        if($u)
        {
            return $u->login;
        }
        return 'anonymous';
    }

    public static function getUser()
    {
        return User::findOne('login = ? AND password = ?', array(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '',
                                                              isset($_SERVER['PHP_AUTH_PW']) ? sha1($_SERVER['PHP_AUTH_PW']) : ''));
    }
}
