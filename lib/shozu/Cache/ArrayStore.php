<?php
namespace shozu\Cache;
/**
 *Array-based cache. Useful for unit testing or keeping object references
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class ArrayStore extends \shozu\Cache
{
    private $cache = array();

    /**
     * No options here.
     *
     * @param Array
     */
    public function __construct(array $options = null)
    {
        // nothing to do here :-)
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
        if($ttl == 0)
        {
            $this->cache[$id] = array($value, 0);
        }
        else
        {
            $expires = time() + $ttl;
            $this->cache[$id] = array($value, $expires);
        }
        return true;
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
        if(!isset($this->cache[$id]))
        {
            return false;
        }
        if($this->cache[$id][1] < time() and $this->cache[$id][1] !== 0)
        {
            $this->cache[$id] = NULL;
            return false;
        }
        return $this->cache[$id][0];
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        if(isset($this->cache[$id]))
        {
            $this->cache[$id] = NULL;
            return true;
        }
        return false;
    }
}