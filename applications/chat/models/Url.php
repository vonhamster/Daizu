<?php
namespace chat\models;
class Url extends \shozu\ActiveBean
{
    const TYPE_LINK = 1;
    const TYPE_IMAGE = 2;
    protected function setTableDefinition()
    {
        $this->isStampable = false;
        $this->addColumn('href');
        $this->addColumn('message_id');
        $this->addColumn('type');
    }

    public static function lastLinks($number)
    {
        return self::find('type = '.self::TYPE_LINK.' order by id desc limit '. (int)$number);
    }

    public static function lastImages($number)
    {
        return self::find('type = '.self::TYPE_IMAGE.' order by id desc limit '. (int)$number);
    }
}