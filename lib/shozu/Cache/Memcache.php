<?php
namespace shozu\Cache;
/**
 * Memcache-based cache.
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class Memcache extends \shozu\Cache
{
    private $options;
    private $memcache;
    /**
     * No options here.
     *
     * @param Array
     */
    public function __construct(array $options = null)
    {
        $this->options = $options;
        if(!is_array($options))
        {
            throw new \shozu\Cache\Exception('You must provide an options array');
        }
        $this->memcache = new \Memcache;
        if(!$this->memcache->connect($options['server'], $options['port']))
        {
            throw new \shozu\Cache\Exception('could not connect to host');
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
        return $this->memcache->set($id, $value, MEMCACHE_COMPRESSED, $ttl);
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
        return $this->memcache->add($id, $value, MEMCACHE_COMPRESSED, $ttl);
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function fetch($id)
    {
        return $this->memcache->get($id);
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        return $this->memcache->delete($id);
    }
}
