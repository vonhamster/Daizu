<?php
namespace shozu;
/**
 * Put benchmark points.
 *
 * Forked from Green framework by Philippe Archambault
 */
final class Benchmark
{
    public static $marks = array();
    public static $enabled = false;
	/**
	 * Benchmark start point
	 *
	 * @param string $name point name
	 * @return boolean
	 */
    public static function start($name)
    {
        if (!self::$enabled)
        {
            return false;
        }
        if (!isset(self::$marks[$name]))
        {
            self::$marks[$name] = array(
                'start' => microtime(true) ,
                'stop' => false,
                'memory_start' => function_exists('memory_get_usage') ? memory_get_usage() : 0,
                'memory_stop' => false
            );
        }
        return true;
    }

	/**
	 * Benchmark stop point
	 *
	 * @param string $name point name
	 * @return boolean
	 */
    public static function stop($name)
    {
        if (!self::$enabled)
        {
            return false;
        }
        if (isset(self::$marks[$name]))
        {
            self::$marks[$name]['stop'] = microtime(true);
            self::$marks[$name]['memory_stop'] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
        }
        return true;
    }
    /**
	 * Get the elapsed time between a start and stop of a mark name, TRUE for all.
	 *
	 * @param string $name
	 * @param integer $decimals
	 * @return array
	 */
    public static function get($name, $decimals = 4)
    {
        if (!self::$enabled)
        {
            return false;
        }
        if ($name === true)
        {
            $times = array();
            foreach(array_keys(self::$marks) as $name)
            {
                $times[$name] = self::get($name, $decimals);
            }
            return $times;
        }
        if (!isset(self::$marks[$name]))
        {
            return false;
        }
        if (self::$marks[$name]['stop'] === false)
        {
            self::stop($name);
        }
        return array(
            'time' => number_format(self::$marks[$name]['stop'] - self::$marks[$name]['start'], $decimals) ,
            'memory' => self::convert_size(self::$marks[$name]['memory_stop'] - self::$marks[$name]['memory_start'])
        );
    }

	/**
	 * Convert byte size in human readable format
	 *
	 * @param integer
	 * @return string
	 */
    public static function convert_size($num)
    {
        if ($num >= 1073741824)
        {
            $num = round($num / 1073741824 * 100) / 100 . ' gb';
        }
        else if ($num >= 1048576)
        {
            $num = round($num / 1048576 * 100) / 100 . ' mb';
        }
        else if ($num >= 1024)
        {
            $num = round($num / 1024 * 100) / 100 . ' kb';
        }
        else
        {
            $num.= ' b';
        }
        return $num;
    }

	/**
	 * Generate HTML-formatted report
	 *
	 * @return string
	 */
    public static function htmlReport()
    {
        if (!self::$enabled)
        {
            return '';
        }
        $html = '<div  style="font-size:14px;font-family:monospace;"><ol>';
        foreach(self::get(true) as $key => $val)
        {
            $html.= '<li><strong>' . htmlspecialchars($key) . '</strong><br/>time&nbsp;&nbsp;: ' . $val['time'] . '<br/>memory: ' . $val['memory'] . '</li>';
        }
        return $html . '</ol></div>';
    }

	/**
	 * Generate CLI/text formatted report
	 *
	 * @return string
	 */
    public static function cliReport()
    {
        $output = '';
        if (!self::$enabled)
        {
            return $output;
        }
        $points = self::get(true);
        if (!empty($points))
        {
            $output.= "\n#### Benchmark ####\n";
            foreach($points as $key => $val)
            {
                $output.= "\n[ " . $key . " ]\n        time: " . $val['time'] . "\n        memory: " . $val['memory'] . "\n";
            }
        }
        return $output;
    }

	/**
	 * Enable benchmark
	 *
	 * @return boolean state
	 */
    public static function enable()
    {
        self::$enabled = true;
        return self::$enabled;
    }

	/**
	 * Disable benchmark
	 *
	 * @return boolean state
	 */
    public static function disable()
    {
        self::$enabled = false;
        return self::$enabled;
    }
}
