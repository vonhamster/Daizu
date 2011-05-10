<?php
namespace chat;
class Application implements \shozu\Application
{
    public static function getRoutes()
    {
        return array(
            '/chat/' => 'chat/index/index',
            '/chat/post/' => 'chat/index/post',
            '/chat/passthru/' => 'chat/index/proxy',
            '/chat/passthru/:any' => 'chat/index/proxy/$1',
            '/chat/upload/' => 'chat/index/upload',
            '/chat/getfile/:any' => 'chat/index/getfile/$1'
        );
    }
    public static function getObservers()
    {
        return array();
    }

    public static function getTranslations($lang_id)
    {
        if($lang_id == 'fr')
        {
            return array(
                'Share' => 'Partager',
                'Send a file' => 'Envoyer un fichier'
            );
        }
        return array();
    }
}