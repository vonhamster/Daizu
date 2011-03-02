<?php
namespace search\models;
class Word extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->isStampable = false;
        $this->addColumn('label', array(
            'type' => 'string',
            'unique' => true
        ));
    }

    public static function fetch($label)
    {
        $instance = self::findOne('label = ?', array($label));
        if(!$instance)
        {
            $instance = new self;
            $instance->setLabel($label);
            $instance->save();
        }
        return $instance;
    }
}