<?php
namespace shozu\Cache;
/**
 * File-based cache.
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class File extends \shozu\Cache
{
    private $path;
    /**
     * New file cache
     *
     * @param Array Options are cache path ('path') and wether to create it ('create')
     */
    public function __construct(array $options = null)
    {
        if(!is_array($options))
        {
            $options = array();
        }
        if(!isset($options['path']))
        {
            $options['path'] = sys_get_temp_dir();
        }
        if(!isset($options['create']))
        {
            $options['create'] = false;
        }

        $slash = substr($options['path'], -1);
        if($slash != '/' and $slash !='\\')
        {
            $options['path'] .= '/';
        }

        if($options['create'])
        {
            if(!is_dir($options['path']))
            {
                if(!mkdir($options['path'], 0755, true))
                {
                    throw new \shozu\Cache\Exception('directory "' . $options['path'] . '" does ot exist and could not be created.');
                }
            }
        }

        $this->path = $options['path'];
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
        $file = $this->fileName($id);
        if($ttl == 0)
        {
            $expires = 0;
        }
        else
        {
            $expires = time() + (int)$ttl;
        }
        if(file_put_contents($file,$expires
            . "\n" . serialize($value)))
        {
            return true;
        }
    }

    /**
     * Add value. Same as store, only will not overwrite existing value
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
        $fileName = $this->fileName($id);
        $old = ini_set('error_reporting', 0);
        if(($file = fopen($fileName, 'r')) === false)
        {
            ini_set('error_reporting', $old);
            return false;
        }
        ini_set('error_reporting', $old);
        $expires = (int)fgets($file);
        if($expires > time() or $expires === 0)
        {
            $data = '';
            while(($line = fgets($file)) !== false)
            {
                $data .= $line;
            }
            fclose($file);
            return unserialize($data);
        }
        fclose($file);
        unlink($fileName);
        return false;
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        $file = $this->fileName($id);
        if(is_file($file))
        {
            return unlink($file);
        }
        return false;
    }

    /**
     * Remove no more valid cache entries
     *
     * @return integer the number of entries removed
     */
    public function clean()
    {
        $erased = 0;
        $files = glob($this->path . '*.cache');
        foreach($files as $file)
        {
            if(($handle = $this->fileHandle($file)) !== false)
            {
                $expires = (int)fgets($handle);
                if($expires < time())
                {
                    fclose($handle);
                    unlink($file);
                    $erased++;
                }
            }
        }
        return $erased;
    }

    private function fileName($id)
    {
        return $this->path . md5($id) . '.cache';
    }

    private function fileHandle($fileName)
    {
        $old = ini_set('error_reporting', 0);
        if(($file = fopen($fileName, 'r')) === false)
        {
            ini_set('error_reporting', $old);
            return false;
        }
        ini_set('error_reporting', $old);
        return $file;
    }
}