<?php
namespace shozu\Cache;
/**
 * APC-based cache. Will not work in CLI.
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class Apc extends \shozu\Cache
{
/**
 * No options here.
 *
 * @param Array
 */
    public function __construct(array $options = null)
    {
        if(!function_exists('apc_store'))
        {
            throw new \shozu\Cache\Exception('APC must be installed to use this backend');
        }
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function store($id, $value, $ttl = 0)
    {
        return apc_store($id, $value, $ttl);
    }

    /**
     * Add value. Same as store, but will not overwrite an existing value.
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function add($id, $value, $ttl = 0)
    {
        if(($val = $this->fetch($id)) === false)
        {
            return $this->store($id, $value, $ttl);
        }
        return false;
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function fetch($id)
    {
        return apc_fetch($id);
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        return apc_delete($id);
    }
}
