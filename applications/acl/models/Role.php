<?php
namespace acl\models;
class Role extends \shozu\ActiveBean
{
    private $resources;
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

    public function getUsers()
    {
        return $this->getRelated('\acl\models\User');
    }

    public function giveResource($resourceLabel)
    {
        $resourceLabel = trim($resourceLabel);
        if(!empty($resourceLabel))
        {
            $this->link(Resource::fetch($resourceLabel));
            $this->resources = null;
            return true;
        }
        return false;
    }

    public function getResources()
    {
        if(is_null($this->resources))
        {
            $this->resources = $this->getRelated('\acl\models\Resource');
        }
        return $this->resources;
    }

    public function removeResource($resourceLabel)
    {
        $resourceLabel = trim($resourceLabel);
        if(!empty($resourceLabel))
        {
            $this->unlink(Resource::fetch($resourceLabel));
            $this->resources = null;
            return true;
        }
        return false;
    }

    public static function fetch($label)
    {
        $instance = self::findOne('label=?',array($label));
        if(!$instance)
        {
            $instance = new Role;
            $instance->label = $label;
            $instance->save();
        }
        return $instance;
    }

    public function hasResource($resourceLabel)
    {
        foreach($this->getResources() as $resource)
        {
            if($resource->label == $resourceLabel)
            {
                return true;
            }
        }
        return false;
    }
}
