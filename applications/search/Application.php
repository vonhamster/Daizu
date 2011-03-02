<?php
namespace search;
class Application implements \shozu\Application
{
    public static function getRoutes()
    {
        return array(
            '/search/' => 'search/service/index',
            '/search/endpoint/' => 'search/service/endpoint',
        );
    }

    public static function getObservers()
    {
        return array();
    }

    public static function getTranslations($lang_id)
    {
        return array();
    }
}