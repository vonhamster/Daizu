<?php
namespace shozu;
/**
 * Cache class with unified API for APC/Array/Disk cache.
 *
 * Uses an API similar to the one exposed by APC, ie fetch/store/delete methods.
 *
 * Instanciation:
 *
 * <code>
 * // disk cache. Create directory if not available.
 * $diskCache = Cache::_('myDiskCache', array('type'   => 'disk'
 *                                               'path'   => '/my/cache/path/',
 *                                               'create' => true));
 * // APC cache. Will throw exception if Apc is not installed
 * $apcCache = Cache::_('myApcCache', array('type' => 'apc'));
 *
 * // Array cache. Destroyed when script ends but useful to keep object references
 * $arrayCache = Cache::_('myArrayCache', array('type' => 'array'));
 * </code>
 *
 * Usage:
 *
 * <code>
 * // store a value for an hour
 * $cache->store('someID', $someValue, 3600);
 *
 * // fetch a value
 * $cache->fetch('someID');
 *
 * // delete a value
 * $cache->delete('someID');
 *
 * // store a value ONLY if id doesn't exist
 * $cache->add('someID', $someValue);
 * </code>
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
abstract class Cache
{
    private static $store = array();

    /**
     * create / get cache instance
     *
     * @param string $id Cache identifier
     * @options array options as key=>value pairs
     * @return Cache cache instance
     */
    public static function getInstance($id, array $options = null)
    {
        if(!isset(self::$store[$id]))
        {
            if(!is_array($options))
            {
                throw new \shozu\Cache\Exception('Options must be passed as an array');
            }
            if(!isset($options['type']))
            {
                throw new \shozu\Cache\Exception('Type option not set');
            }
            switch($options['type'])
            {
                case 'store':
                case 'array':
                    self::$store[$id] = new \shozu\Cache\ArrayStore($options);
                    break;
                case 'ram':
                case 'apc':
                    self::$store[$id] = new \shozu\Cache\Apc($options);
                    break;
                case 'file':
                case 'disk':
                    self::$store[$id] = new \shozu\Cache\File($options);
                    break;
                case 'memcache':
                case 'memcached':
                    self::$store[$id] = new \shozu\Cache\Memcache($options);
                default:
                    throw new \shozu\Cache\Exception('Unsupported cache type');
                    break;
            }
        }
        return self::$store[$id];
    }

    /**
     * Convinience shortcut to Cache::getInstance()
     *
     * @param string $id Cache identifier
     * @options array options as key=>value pairs
     * @return Cache cache instance
     */
    public static function _($id, array $options = null)
    {
        return self::getInstance($id, $options);
    }

    abstract public function __construct(array $options = null);
    abstract public function store($id, $value, $ttl = 0);
    abstract public function add($id, $value, $ttl = 0);
    abstract public function fetch($id);
    abstract public function delete($id);
}