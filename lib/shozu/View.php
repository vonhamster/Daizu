<?php
namespace shozu;
/**
 * View
 *
 * @package MVC
 */
class View
{
    /**
     *  String of template file
     */
    private $file;
    /**
     * Array of template variables
     */
    private $vars = array();


    private $cache_id;

    /**
     * Assign the template path
     *
     * @param string $file Template path (absolute path or path relative to the templates dir)
     * @param array $vars assigned variables
     */
    public function __construct($file, $vars = false)
    {
        $this->file = $file;
        if (!file_exists($this->file))
        {
            throw new \Exception("View '{$this->file}' not found!");
        }
        if ($vars !== false)
        {
            $this->vars = $vars;
        }
    }

    /**
     * Assign specific variable to the template
     *
     * <code>
     * // assign single var
     * $view->assign('varname', 'varvalue');
     * // assign array of vars
     * $view->assign(array('varname1' => 'varvalue1', 'varname2' => 'varvalue2'));
     * </code>
     *
     * @param mixed $name Variable name
     * @param mixed $value Variable value
     */
    public function assign($name, $value = null)
    {
        if (is_array($name))
        {
            array_merge($this->vars, $name);
        }
        else
        {
            $this->vars[$name] = $value;
        }
    }


    /**
     * Return template output as string
     *
     * @return string content of compiled view template
     */
    public function render($stripUselessSpaces = false)
    {
        ob_start();
        extract($this->vars, EXTR_SKIP);
        include $this->file;
        $content = ob_get_clean();
        if($stripUselessSpaces)
        {
            $content = self::stripUselessSpace($content);
        }
        return $content;
    }

    /**
     * Display (echoes) the rendered template
     */
    public function display($stripUselessSpaces = false)
    {
        echo $this->render($stripUselessSpaces);
    }

    /**
     * Render the content and return it
     *
     * <code>
     * echo new View('blog', array('title' => 'My title'));
     * </code>
     *
     * @return string content of the view
     */
    public function __toString()
    {
        //return $this->render();
        ///*
        try
        {
            $res = $this->render();
            return $res;
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
        //*/
    }

    /**
     * Render action in view
     *
     * <code>
     * echo $view->action('blog', 'index', 'post', array($post_uid));
     * // alternative arguments
     * echo $view->action('blog/index/post', array($post_uid));
     * </code>
     *
     * @param string $application application name
     * @param string $controller controller name
     * @param string $action action name
     * @param array $params request parameters
     * @return string
     */
    public function action()
    {
        $params = array();
        $arg_v = func_get_args();
        $arg_c = func_num_args();
        if($arg_c > 2)
        {
            $application = $arg_v[0];
            $controller = $arg_v[1];
            $action = $arg_v[2];
            if($arg_c > 3)
            {
                $params = (array)$arg_v[3];
            }
        }
        else
        {
            list($application, $controller, $action) = explode('/', $arg_v[0]);
            if($arg_c == 2)
            {
                $params = (array)$arg_v[1];
            }
        }
        return \shozu\Dispatcher::render($application, $controller, $action, $params);
    }

    /**
     * Escape HTML special chars
     *
     * @param string
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string);
    }

    /**
     * Limit string to given length but do not truncate words
     *
     * @param string $str input string
     * @param integer $length length limit
     * @param integer $minword
     * @return string
     */
    public function limit($str, $length, $minword = 3)
    {
        $sub = '';
        $len = 0;
        foreach (explode(' ', $str) as $word)
        {
            $part = (($sub != '') ? ' ' : '') . $word;
            $sub .= $part;
            $len += strlen($part);
            if (strlen($word) > $minword && strlen($sub) >= $length)
            {
                break;
            }
        }
        return $sub . (($len < strlen($str)) ? '...' : '');
    }

    /**
     * Multibyte-aware ucfirst.
     *
     * Uppercase first letter
     *
     * @param string $str
     * @param string $e encoding, defaults to utf-8
     * @return string
     */
    public function ucfirst($str, $e = 'utf-8')
    {
        $fc = mb_strtoupper(mb_substr($str, 0, 1, $e), $e);
        return $fc . mb_substr($str, 1, mb_strlen($str, $e), $e);
    }


    /**
     * Translate helper
     *
     * @param string
     * @return string
     */
    public function T($string, $escape_html = true)
    {
        $shozu = \shozu\Shozu::getInstance();
        if($shozu->use_i18n)
        {
            if(isset($shozu->translations[$string]))
            {
                $string = $shozu->translations[$string];
            }
            elseif($shozu->debug)
            {
                $string = '***' . $string;
            }
        }
        if($escape_html)
        {
            $string = $this->escape($string);
        }
        return $string;
    }

    /**
     * Generate URL
     *
     * @param string $target application/controller/action
     * @param array $params action parameters
     * @param array $site website
     * @return string
     */
    public function url($target, array $params = null, $site = null)
    {
        return \shozu\Shozu::getInstance()->url($target, $params, $site);
    }

    private static function stripUselessSpace($html)
    {
        return preg_replace('#(?:(?:(^|>[^<]*?)[\t\s\r\n]*)|(?:[\t\s\r\n]*(<|$)))#', '$1$2', $html);
    }



    /**
     * Cache portions of a view. Usage:
     *
     * <code>
     * <?php if($this->cacheBegin('myCacheId')){ ?>
     * <!-- some dynamic content here will be cached for 600 seconds -->
     * <?php $this->cacheEnd(600);} ?>
     * </code>
     *
     * @param string $id
     * @return boolean
     */
    public function cacheBegin($id)
    {
        $cache = self::getCache();
        $this->cache_id = $id;
        if(($contentFromCache = $cache->fetch($id)) === false)
        {
            ob_start();
            return true;
        }
        else
        {
            echo $contentFromCache;
            return false;
        }
    }

    /**
     *
     * @param integer $ttl
     */
    public function cacheEnd($ttl = 0)
    {
        $cache = self::getCache();
        if(($contentFromCache = $cache->fetch($this->cache_id)) === false)
        {
            $contentToCache = ob_get_contents();
            $cache->store($this->cache_id, $contentToCache, $ttl);
            ob_end_clean();
            echo $contentToCache;
        }
        else
        {
            ob_end_clean();
        }
    }


    private static function getCache()
    {
        if(function_exists('apc_fetch'))
        {
            return \shozu\Cache::getInstance('view_cache', array('type' => 'apc'));
        }
        return \shozu\Cache::getInstance('view_cache', array(
            'type'   => 'disk',
            'path'   => sys_get_temp_dir(),
            'create' => false));
    }
}
