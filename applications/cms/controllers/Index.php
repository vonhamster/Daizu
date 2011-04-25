<?php
namespace cms\controllers;
use \cms\models\Page as Page;
use \cms\models\Comment as Comment;
use \shozu\Benchmark as Benchmark;
class Index extends \shozu\Controller
{
    public function norouteAction(\Exception $e)
    {
        $s = \shozu\Shozu::getInstance();
        if($e->getCode() == 404)
        {
            Benchmark::start(__CLASS__.'::'.__FUNCTION__.': find published page');
            $cache_key = Page::mkObjectCacheKey(\shozu\Dispatcher::getCurrentUrl());
            if(($page = Page::getCache()->fetch($cache_key)) === false)
            {
                $page = Page::findPublishedPageByUrl(\shozu\Dispatcher::getCurrentUrl());
                Page::getCache()->store($cache_key, $page);
            }
            Benchmark::stop(__CLASS__.'::'.__FUNCTION__.': find published page');
            if($page)
            {
                Benchmark::start(__CLASS__.'::'.__FUNCTION__.': find template');
                $template = $page->getTemplate();
                $template_path = $s->document_root . 'themes/' . \DAIZU_THEME . '/templates/' . $template . '.php';
                if(!is_file($template_path))
                {
                    $template_path = __DIR__ . '/../views/default/templates/default.php';
                }
                Benchmark::stop(__CLASS__.'::'.__FUNCTION__.': find template');
                Benchmark::start(__CLASS__.'::'.__FUNCTION__.': find layout');
                $layout = $page->getLayout();
                $layout_path = $s->document_root . 'themes/' . \DAIZU_THEME . '/layouts/' . $layout . '.php';
                if(!is_file($layout_path))
                {
                    $layout_path = __DIR__ . '/../views/default/layouts/default.php';
                }
                Benchmark::stop(__CLASS__.'::'.__FUNCTION__.': find layout');
                $this->setLayout($layout_path);
                $this->assignToLayout('title', $page->getSeo_title() ?: $page->getTitle());
                $this->assignToLayout('description', $page->getSeo_description() ?: '');
                $this->assignToLayout('keywords', $page->getSeo_keywords() ?: '');
                $this->assignToLayout('author', $page->getAuthor() ?: '');
                
                $output = $this->render($template_path, array('page' => $page));
                
                if($page->getMake_static_file())
                {
                    $file_path = $page->makeStaticFilePath();
                    if(!is_file($file_path))
                    {
                        if(!is_dir(dirname($file_path)))
                        {
                            mkdir(dirname($file_path), 0777, true);
                        }
                        file_put_contents($file_path, $output);
                    }
                }

                die($output);
            }
            else
            {
                $template_path = $s->document_root . 'themes/' . \DAIZU_THEME . '/error.php';
                if(!is_file($template_path))
                {
                    $template_path = __DIR__ . '/../views/default/error.php';
                }
                header('HTTP/1.0 404 Not Found');
                $this->display($template_path, array('error' => $e));
            }
        }
        else
        {
            $template_path = $s->document_root . 'themes/' . \DAIZU_THEME . '/error.php';
            if(!is_file($template_path))
            {
                $template_path = __DIR__ . '/../views/default/error.php';
            }
            header('HTTP/1.0 500 internal error');
            $this->display($template_path, array('error' => $e));
        }
    }

    public function postcommentAction()
    {
        $this->floodLimit('daizu post comment', 2, 60);
        $page = Page::findOne('id = ?', array($this->getParam('page_id')));
        if($page)
        {
            try
            {
                $comment = new Comment;
                $comment->setPage_id($page->getId());
                $comment->setName($this->getParam('name'));
                $comment->setEmail($this->getParam('email'));
                $comment->setUrl($this->getParam('website'));
                $comment->setContent($this->getParam('comment'));
                $comment->save();
                \shozu\Observer::notify('daizu.comment.new', $comment, $page);
                die(json_encode(array('response' => 'ok')));
            }
            catch(\Exception $e)
            {
                $v = new \shozu\View(__FILE__);
                die(json_encode(array('response' => 'ko', 'message' => $v->T('Could not save your comment. Please check the fields.'))));
            }
        }
        throw new \Exception('no such page', 404);
    }

    public function contentrssAction($number_of_items = 10, $keyword = '')
    {
        if(!empty($keyword))
        {
            $items = \cms\models\Page::findLastPublicationsByKeyword($keyword, $number_of_items);
            if(!count($items))
            {
                throw new \Exception('page not found', 404);
            }
            $this->display('default/contentrss', array('feed_name' => $keyword, 'pages' => $items));
        }
        else
        {
            $this->display('default/contentrss', array('pages' => \cms\models\Page::findLastPublications($number_of_items)));
        }
    }
}
