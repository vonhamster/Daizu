<?php
namespace shozu;
/**
 * Very basic flood protection based on client IP. Don't trust this !
 * This might suffice for common vandalism, but you'd better implement a
 * proven solution way down your stack (the sooner, the better).
 */
class AntiFlood
{
    /**
     * Throws an exception if resource has been hit too many times in a
     * given period
     *
     * <code>
     * // only access resource 5 times in a minute
     * AntiFlood::limit('myResourceId', 5, 60);
     * </code>
     *
     * @param string $resource_id identifier for protected resource
     * @param integer $max_requests max number of requests
     * @param integer $time_slice time period (in seconds)
     */
    public static function limit($resource_id, $max_requests = 5, $time_slice = 60, Cache $cache = null)
    {
        if(!self::hasAccess($resource_id, $max_requests, $time_slice, $cache))
        {
            throw new \shozu\AntiFlood\Exception('Flood detected', 403);
        }
    }

    public static function hasAccess($resource, $max_requests = 5, $time_slice = 60, Cache $cache = null)
    {
        if(strtolower(php_sapi_name()) == 'cli')
        {
            return true;
        }
        if(is_null($cache))
        {
            if(function_exists('apc_fetch'))
            {
                $cache = Cache::_('anti-flood cache', array('type' => 'apc'));
            }
            else
            {
                $cache = Cache::_('anti-flood cache', array('type' => 'disk'));
            }
        }

        $now = time();
        $cache_key = 'antiflood - ' . $resource . ' - ' . getenv('REMOTE_ADDR');
        $infos = $cache->fetch($cache_key);
        if(!$infos)
        {
            $infos = array(
                'start' => $now,
                'count' => 1
            );
            $cache->store($cache_key,$infos, $time_slice);
            return true;
        }
        $infos['count']++;
        if($infos['count'] > $max_requests)
        {
            $elapsed = $now - $infos['start'];
            if($elapsed > $time_slice)
            {
                $infos['count'] = 1;
                $infos['start'] = $now;
                $cache->store($cache_key,$infos, $time_slice);
                return true;
            }
            else
            {
                return false;
            }
        }
        $cache->store($cache_key,$infos, $time_slice);
        return true;
    }
}
