<?php
namespace shozu;
interface Application
{
    /**
     * @return array
     */
    public static function getRoutes();

    /**
     * @return array
     */
    public static function getObservers();

    /**
     *
     * @param string lang id (fr, en, de, etc)
     * @return array
     */
    public static function getTranslations($lang_id);
}