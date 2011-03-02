<?php
namespace acl\models;
class Resource extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->addColumn('label', array(
            'type' => 'string',
            'length' => 64,
            'unique' => true,
            'validators' => array('notblank'),
            'formatters' => array('trim', 'nodiacritics', 'lowercase')
        ));
    }

    public static function fetch($label)
    {
        $instance = self::findOne('label=?', array($label));
        if(!$instance)
        {
            $instance = new Resource;
            $instance->label = $label;
            $instance->save();
        }
        return $instance;
    }
}
