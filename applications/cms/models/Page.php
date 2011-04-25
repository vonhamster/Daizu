<?php
namespace cms\models;
use \cms\models\Comment as Comment;
use \shozu\Benchmark as Benchmark;
use \shozu\Observer as Observer;
class Page extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->addColumn('user_id', array(
            'type' => 'integer'
        ));
        $this->addColumn('title', array(
            'validators' => array('notblank'),
            'formatters' => array('trim')
        ));
        $this->addColumn('url',array(
            'unique' => true,
            'formatters' => array('trim', 'addStartSlash'),
            'validators' => array('isNotFrameworkRoute')
        ));
        $this->addColumn('heading');
        $this->addColumn('body');
        $this->addColumn('published', array(
            'type' => 'boolean',
            'default' => '0'
        ));
        $this->addColumn('published_from', array(
            'type' => 'datetime'
        ));
        $this->addColumn('published_to', array(
            'type' => 'datetime'
        ));
        $this->addColumn('parent_id', array(
            'type' => 'integer'
        ));
        $this->addColumn('order_number', array(
            'type' => 'integer'
        ));
        $this->addColumn('template', array(
            'type' => 'string'
        ));
        $this->addColumn('layout', array(
            'type' => 'string'
        ));
        $this->addColumn('seo_title');
        $this->addColumn('seo_description');
        $this->addColumn('seo_keywords');
        $this->addColumn('allow_comments', array(
            'type' => 'boolean',
            'default' => '0'
        ));
        $this->addColumn('author');
        $this->addColumn('versions', array(
            'type' => 'array',
            'default' => json_encode(array())
        ));
        $this->addColumn('analyzer', array(
            'default' => 'standard'
        ));
        $this->addColumn('make_static_file', array(
            'type' => 'boolean',
            'default' => '0'
        ));
    }

    /**
     * Get children pages
     *
     * @return Iterator
     */
    public function getChildren()
    {
        $now = date('Y-m-d H:i:s', time());
        return self::find('parent_id = ? order by order_number',
            array($this->getId()));
    }

    /**
     * Get published children pages
     *
     * @return Iterator
     */
    public function getPublishedChildren()
    {
        $now = date('Y-m-d H:i:s', time());
        return self::find('parent_id = ? and published = "1" and published_from < ? and published_to > ? order by order_number',
            array($this->getId(), $now, $now));
    }

    /**
     * Return array of siblings
     *
     * @return Iterator
     */
    public function getSiblings()
    {
        return self::find('parent_id = ? and id != ? order by order_number',
            array($this->getParent_id(), $this->getId()));
    }

    /**
     * Get published siblings pages
     *
     * @return Iterator
     */
    public function getPublishedSiblings()
    {
        $now = date('Y-m-d H:i:s', time());
        return self::find('parent_id = ? and id != ? and published = "1" and published_from < ? and published_to > ? order by order_number',
            array($this->getParent_id(), $this->getId(), $now, $now));
    }

    /**
     *
     * @param boolean $refresh
     * @return mixed Page or false
     */
    public function getPublishedLeftSibling($refresh = false)
    {
        $now = date('Y-m-d H:i:s', time());
        return self::findOne('parent_id = ? and order_number < ? and published = "1" and published_from < ? and published_to > ? order by order_number desc',
                                        array(
                                            $this->getParent_id(),
                                            $this->getOrder_number(),
                                            $now,
                                            $now
                                        ));
    }

    /**
     *
     * @param boolean $refresh
     * @return mixed Page or false
     */
    public function getPublishedRightSibling($refresh = false)
    {
        $now = date('Y-m-d H:i:s', time());
        return self::findOne('parent_id = ? and order_number > ? and published = "1" and published_from < ? and published_to > ? order by order_number',
                                        array(
                                            $this->getParent_id(),
                                            $this->getOrder_number(),
                                            $now,
                                            $now
                                        ));
    }

    /**
     * Get parent page
     *
     * @param boolean $refresh Force refresh from database
     * @return Page parent page
     */
    public function getParent()
    {
        return self::findOne('id = ?', array($this->getParent_id()));
    }

    /**
     * Return array of ancestors
     *
     * @param boolean $refresh
     * @return array
     */
    public function getAncestors($refresh = false)
    {
        $ancestors = array();
        $page = $this;
        while(($page = $page->getParent($refresh)) !== false)
        {
            $ancestors[] = $page;
        }
        return array_reverse($ancestors);
    }

    /**
     * Return an array of comments
     *
     * @return array
     */
    public function getComments()
    {
        return Comment::findCommentsForPage($this);
    }

    /**
     * Get published siblings pages
     *
     * @return array array of Pages
     */
    public function getPublishedComments()
    {
        return Comment::findPublishedCommentsForPage($this);
    }

    /**
     * Save as child
     *
     * @throws \Exception if parent has not been saved
     * @param Page $parent_page
     */
    public function saveAsChildOf(Page $parent_page, $position = null)
    {
        if(is_null($parent_page->getId()))
        {
            throw new \Exception('parent id is not available');
        }
        $parent_page->deleteAllCache();
        $this->setParent_id($parent_page->getId());
        if(is_null($position))
        {
            try
            {
                $q = self::getPDO()
                    ->query('SELECT max(order_number) + 1 FROM cmspage where parent_id = ' . $parent_page->getId())
                    ->fetch(\PDO::FETCH_NUM);
                $position = $q[0];
            }
            catch(\Exception $e)
            {
                $position = 10000;
            }
        }
        $this->setOrder_number($position);
        $this->save();
    }

    protected function preSave()
    {
        // set url
        $url = $this->getUrl();
        if(empty($url))
        {
            $this->setUrl('/NEWNODE/' . date('Y-m-d_H-i-s/'));
        }
        elseif(substr($url, 0, 9) == '/NEWNODE/')
        {
            $url = '/';
            $ancestors = $this->getAncestors();
            foreach($ancestors as $ancestor)
            {
                if($ancestor->getUrl() != '/')
                {
                    $url .= self::slugify($ancestor->getTitle()) . '/';
                }
            }
            $this->setUrl($url . self::slugify($this->getTitle()) . '/');
        }

        // set publication dates
        $date_from = $this->getPublished_from();
        if(empty($date_from))
        {
            $this->setPublished_from(date('Y-m-d H:i:s',time()));
        }
        $date_to = $this->getPublished_to();
        if(empty($date_to))
        {
            $this->setPublished_to(date('Y-m-d H:i:s',time() + 24 * 3600 * 365 * 10));
        }

        // set views
        $layout = $this->getLayout();
        if(empty($layout))
        {
            $parent = $this->getParent();
            if($parent)
            {
                $this->setLayout($parent->getLayout());
            }
            else
            {
                $this->setLayout('default');
            }
        }
        $template = $this->getTemplate();
        if(empty($template))
        {
            $parent = $this->getParent();
            if($parent)
            {
                $this->setTemplate($parent->getTemplate());
            }
            else
            {
                $this->setTemplate('default');
            }
        }

        // add current to versions
        $versions = $this->getVersions();
        if(!is_array($versions))
        {
            $versions = array();
        }
        $current = $this->toArray();
        unset($current['versions']);
        unset($current['parent_id']);
        unset($current['order_number']);
        $current['published_from'] = $current['published_from']->format('Y-m-d H:i:s');
        $current['published_to'] = $current['published_to']->format('Y-m-d H:i:s');
        $current['created_at'] = $current['created_at'] instanceof \DateTime ? $current['created_at']->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $current['modified_at'] = $current['modified_at'] instanceof \DateTime ? $current['modified_at']->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $versions[] = $current;
        $this->setVersions($versions);
        //$this->setDescendantsPublishing($this->getPublished());
        Observer::notify('daizu.page.presave', $this);
    }

    protected function postSave()
    {
        $this->deleteAllCache();
        $parent = $this->getParent();
        if($parent)
        {
            $parent->deleteAllCache();
        }
        Observer::notify('daizu.page.postsave', $this);
    }


    /**
     *
     * @return boolean
     */
    public function deleteAllCache()
    {
        $this->deleteStaticFile();
        self::getCache()->delete(self::mkObjectCacheKey($this->getUrl()));
        self::getCache()->delete($this->mkViewCacheKey());
        return true;
    }
    
    /**
     * 
     * @return boolean
     */
    public function deleteStaticFile()
    {
        $path = $this->makeStaticFilePath();
        if(is_file($path))
        {
            unlink($path);
        }
        if(substr($path, -11) == '/index.html')
        {
            if(is_dir(dirname($path)))
            {
                rmdir(dirname($path));
            }
        }
        return true;
    }
    
    
    public function makeStaticFilePath()
    {
        $s = \shozu\Shozu::getInstance();
        $forbidden_paths = array(
            $s->document_root . 'index.php',
            $s->document_root . 'static',
            $s->document_root . 'themes',
            $s->document_root . 'upload'
        );
        $file_path = $s->document_root . substr($this->getUrl(),1);
        foreach ($forbidden_paths as $forbidden)
        {
            if(strpos($file_path,$forbidden))
            {
                throw new \Exception('forbidden path');
            }
        }
        if(substr($file_path, -1) == '/')
        {
            $file_path .= 'index.html';
        }
        if(substr($file_path,-5) != '.html')
        {
            $file_path .= '/index.html';
        }
        return $file_path;
    }
    
    /**
     *
     * @return string
     */
    public function getFullUrl()
    {
        $s = \shozu\Shozu::getInstance();
        if($this->getUrl() == '/')
        {
            return $s->base_url;
        }
        if($s->url_rewriting)
        {
            return $s->base_url . substr($this->getUrl(),1);
        }
        return $s->base_url . '?' . $this->getUrl();
    }

    /**
     *
     * @return boolean
     */
    public function isPublished()
    {
        if(!$this->getPublished())
        {
            return false;
        }
        $now = time();
        $from = $this->getPublished_from()->getTimestamp();
        $to = $this->getPublished_to()->getTimestamp();
        if($now > $to || $now < $from)
        {
            return false;
        }
        return true;
    }

    /**
     *
     * @return string
     */
    public function renderForm()
    {
        $v = new \shozu\View(__DIR__.'/../views/edit_page.php',
            array('page' => $this));
        return $v->render();
    }

    /**
     * Find published page by url
     *
     * @param string $url
     * @return Page
     */
    public static function findPublishedPageByUrl($url)
    {
        $now = date('Y-m-d H:i:s');
        return self::findOne('published= "1" and published_from < ? and published_to > ? and url = ?',
            array($now, $now, $url));
    }


    /**
     *
     * @param integer $limit
     * @return array
     */
    public static function findLastPublications($limit = 10)
    {
        $now = date('Y-m-d H:i:s');
        return self::find('published = "1" and published_from < ? and published_to > ? order by published_from desc limit '.(int)$limit,
            array($now, $now));
    }

    /**
     *
     * @param Page $parent
     * @param integer $limit
     * @return array
     */
    public function findLastPublicationsUnderParent(Page $parent, $limit)
    {
        $now = date('Y-m-d H:i:s');
        $id = $parent->getId();
        return self::find('published = 1 and published_from < ? and published_to > ? and parent_id = ? order by published_from desc limit '.(int)$limit,
            array($now, $now, $id));
    }

    /**
     *
     * @param string $keyword
     * @param integer $limit
     * @return array
     */
    public static function findLastPublicationsByKeyword($keyword, $limit = 10)
    {
        $now = date('Y-m-d H:i:s');
        return self::find('published = "1" and published_from < ? and published_to > ?  and seo_keywords like ? order by published_from desc limit '.(int)$limit,
            array($now, $now, '%'.$keyword.'%'));
    }

    /**
     *
     * @return Page
     */
    public static function fetchRoot()
    {
        $root = self::findOne('url = ?', array('/'));
        if(!$root)
        {
            $root = new Page;
            $root->setTitle('Homepage');
            $root->setBody('<p>This is your Homepage.</p>');
            $root->setUrl('/');
            $root->setPublished(true);
            $root->setOrder_number(0);
            $root->save();
        }
        return $root;
    }

    /**
     *
     * @param string $in
     * @return string
     */
    public static function slugify($in)
    {
        $out = str_replace(array('.','_'), array('-','-'), \shozu\Inflector::fileName($in));
        $out = str_replace('--','-', $out);
        $out = str_replace('--','-', $out);
        while(substr($out, -1) == '-')
        {
            $out = substr($out, 0, -1);
        }
        while(substr($out, 0, 1) == '-')
        {
            $out = substr($out, 1);
        }
        return $out;
    }

    /**
     *
     * @param string $in
     * @return string
     */
    protected static function addStartSlashFormatter($in)
    {
        if(substr($in, 0, 1) !== '/')
        {
            $in = '/' . $in;
        }
        return $in;
    }

    /**
     *
     * @param string $in
     * @return boolean
     */
    protected static function isNotFrameworkRouteValidator($in)
    {
        if(in_array($in,array(
            '/index.php','/static/','/static','/themes/','/themes','/upload/','/upload'
        )))
        {
            return false;
        }
        return !in_array($in, \shozu\Shozu::getInstance()->routes);
    }


    /**
     *
     * @return \shozu\Cache
     */
    public static function getCache()
    {
        if(function_exists('apc_fetch'))
        {
            return \shozu\Cache::getInstance('myApcCache', array('type' => 'apc'));
        }
        return \shozu\Cache::getInstance('myDiskCache', array('type' => 'file'));
    }

    /**
     *
     * @param string $url
     * @return string
     */
    public static function mkObjectCacheKey($url)
    {
        return 'daizu-' . self::getSiteIdentifier() . '-page-object-'.$url;
    }

    /**
     *
     * @return string
     */
    public function mkViewCacheKey()
    {
        return 'daizu-' . self::getSiteIdentifier() . '-page-'.$this->getId();
    }


    public function synchronizeDescendantsPublishing()
    {
        foreach($this->getChildren() as $child)
        {
            self::recursePublishing($child, 
                $this->getPublished(),
                $this->getPublished_from()->format('Y-m-d H:i:s'),
                $this->getPublished_to()->format('Y-m-d H:i:s'));
        }
    }

    private static function recursePublishing(Page $parent, $published, $published_from, $published_to)
    {
        $parent->setPublished($published);
        $parent->setPublished_from($published_from);
        $parent->setPublished_to($published_to);
        $parent->save();
        foreach($parent->getChildren() as $child)
        {
            self::recursePublishing($child, $published, $published_from, $published_to);
        }
    }

    protected function preDelete()
    {
        $this->deleteAllCache();
        $parent = $this->getParent();
        if($parent)
        {
            $parent->deleteAllCache();
        }
    }

    private static function getSiteIdentifier()
    {
        return \DAIZU_SITE_NAME;//@todo remove global 
    }
}