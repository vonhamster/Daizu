<?php
namespace acl\models;
class User extends \shozu\ActiveBean
{
    private $roles;
    private $resources;

    protected function setTableDefinition()
    {
        $this->addColumn('login', array(
            'type' => 'string',
            'length' => 64,
            'unique' => true,
            'validators' => array('notblank'),
            'formatters' => array('trim')
        ));
        $this->addColumn('password', array(
            'type'       => 'string',
            'length'     => 40,
            'validators' => array('notblank'),
            'formatters' => array('trim')
        ));
        $this->addColumn('store', array(
            'type' => 'array'
        ));
    }

    /**
     * Give role to user
     *
     * @param string
     * @return boolean
     */
    public function giveRole($roleLabel)
    {
        $roleLabel = trim($roleLabel);
        if(!empty($roleLabel))
        {
            $this->link(Role::fetch($roleLabel));
            $this->roles = null;
            return true;
        }
        return false;
    }

    /**
     * Remove role from user
     *
     * @param string
     * @return boolean
     */
    public function removeRole($roleLabel)
    {
        $roleLabel = trim($roleLabel);
        if(!empty($roleLabel))
        {
            $this->unlink(Role::fetch($roleLabel));
            $this->roles = null;
            return true;
        }
        return false;
    }

    /**
     * Determine if User has given Role
     *
     * @param string $roleLabel Role label
     * @return boolean
     */
    public function hasRole($roleLabel)
    {
        $roleLabel = strtolower($roleLabel);
        foreach($this->getRoles() as $role)
        {
            if($role->label == $roleLabel)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Get roles associated to the user
     *
     * @return array array of Role
     */
    public function getRoles()
    {
        if(is_null($this->roles))
        {
            $this->roles = $this->getRelated('\acl\models\Role');
        }
        return $this->roles;
    }

    /**
     * Determine if User has given resource
     *
     * @param string $resourceLabel Resource label
     * @return boolean
     */
    public function hasResource($resourceLabel)
    {
        $resourceLabel = strtolower($resourceLabel);
        foreach($this->getRoles() as $role)
        {
            if($role->hasResource($resourceLabel))
            {
                return true;
            }
        }
        if(is_null($this->resources))
        {
            $this->resources = $this->getRelated('\acl\models\Resource');
        }
        foreach($this->resources as $resource)
        {
            if($resource->label == $resourceLabel)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Store/Fetch data
     *
     * <code>
     * // fetch value
     * $user->store($key);
     * // store value
     * $user->store($key, $value);
     * </code>
     *
     * @param string key
     * @param mixed value
     * @return mixed
     */
    public function store()
    {
        $nbargs = func_num_args();
        $a = (array)$this->store;
        if($nbargs === 2)
        {
            $a[func_get_arg(0)] = func_get_arg(1);
            $this->store = $a;
            return $a[func_get_arg(0)];
        }
        elseif($nbargs === 1)
        {
            if(isset($a[func_get_arg(0)]))
            {
                return $a[func_get_arg(0)];
            }
        }
        return null;
    }
}
