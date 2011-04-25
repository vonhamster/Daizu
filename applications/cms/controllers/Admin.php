<?php
namespace cms\controllers;
use \cms\models\Page as Page;
use \cms\models\Comment as Comment;
use \acl\lib\BasicAuth as Auth;
class Admin extends \shozu\Controller
{
    public function indexAction()
    {
        Auth::mustHave('cms.read');
        $this->setLayout('admin');
        $s = \shozu\Shozu::getInstance();
        $this->display('edit_site');
    }

    public function editpageAction()
    {
        Auth::mustHave('cms.update');
        $t = new \shozu\Taconite();
        $page = Page::findOne('id = ?', array($this->getParam('id')));
        if($page)
        {
            $t->replaceContent('#editPage', $page->renderForm());
            $t->js('
                $("#page_body").markItUp(mySettings);
                //$("#page_body").elrte({toolbar:"compact"});
                $(".datetime").datePicker();
                $("#pageform").ajaxForm();
                $(".comment_published_state").bind("change", function(){
                   $.post("'.\shozu\Shozu::getInstance()->url('cms/admin/togglecommentpublishing').'", {id:$(this).attr("id"), published:$(this).is(":checked")});
                });
                $("a.loadversion").bind("click", function(){
                    $.post($(this).attr("href"), function(data){
                        $("#page_url").val(data.url);
                        $("#page_author").val(data.author);
                        $("#page_body").val(data.body);
                        $("#page_heading").val(data.heading);
                        $("#page_layout").val(data.layout);
                        $("#page_template").val(data.template);
                        $("#page_published_from").val(data.published_from);
                        $("#page_published_to").val(data.published_to);
                        $("#page_seo_description").val(data.seo_description);
                        $("#page_seo_title").val(data.seo_title);
                        $("#page_allow_comments").attr("checked", data.allow_comments);
                    });
                    return false;
                });
                $("#toggle_version_list").bind("click", function(){
                    $("#version_list").toggle("fast");
                });
                $("#toggle_seo_fields").bind("click", function(){
                    $("#seo_fields").toggle("fast");
                });
                $("#toggle_indexing_fields").bind("click", function(){
                    $("#indexing_fields").toggle("fast");
                });
                $("#page_url").change(function(){
                    $("#link_to_page").hide();
                });
            ');
        }
        $t->render();
    }

    public function loadpageversionAction($page_id, $version_index)
    {
        Auth::mustHave('cms.read');
        $page = Page::findOne('id = ?', array($page_id));
        if($page)
        {
            foreach ($page->getVersions() as $idx => $version)
            {
                if($idx == $version_index)
                {
                    header('content-type: application/json');
                    die(json_encode($version));
                }
            }
        }
        throw new \Exception('not foud', 404);
    }

    public function togglecommentpublishingAction()
    {
        $comment = Comment::findOne('id = ?', array(str_replace('comment_published_','', $this->getParam('id'))));
        if($comment)
        {
            $comment->setPublished(!$comment->getPublished());
            $comment->save();
        }
        die;
    }

    public function deletepageAction()
    {
        Auth::mustHave('cms.delete');
        $page = Page::findOne('id = ?', array($this->getParam('id')));
        if($page)
        {
            if(count($page->getChildren()) === 0 && $page->getUrl() != '/')
            {
                $page->deleteAllCache();
                $page->delete();
                header('content-type: application/json');
                die(json_encode(array('status' => 'ok')));
            }
            else
            {
                header('content-type: application/json');
                die(json_encode(array('err' => 'protected page')));
            }
        }
    }

    public function newpageAction()
    {
        Auth::mustHave('cms.create');
        $parent = Page::findOne('id = ?', array($this->getParam('parent')));
        if($parent)
        {
            $page = new Page;
            $page->setTitle('New page');
            $page->setPublished(false);
            $page->setPublished_from($parent->getPublished_from());
            $page->setPublished_to($parent->getPublished_to());
            $page->saveAsChildOf($parent);
            header('content-type: application/json');
            die(json_encode(array('status' => 'ok', 'id' => $page->getId())));
        }
    }

    public function movepageAction()
    {
        Auth::mustHave('cms.update');
        $parent = Page::findOne('id = ?', array($this->getParam('parent')));
        if($parent)
        {
            $children_ids = explode(',', $this->getParam('children'));
            $i = 1;
            foreach($children_ids as $child_id)
            {
                $child = Page::findOne('id = ?', array($child_id));
                if($child && $child_id != $parent->getId())
                {
                    $child->saveAsChildOf($parent, $i);
                    $i++;
                }
            }
            header('content-type: application/json');
            die(json_encode(array('status' => 'ok')));
        }
    }

    public function renamepageAction()
    {
        Auth::mustHave('cms.update');
        $page = Page::findOne('id = ?', array($this->getParam('node_id')));
        $name = trim($this->getParam('name'));
        if($page && !empty($name))
        {
            $page->setTitle($this->getParam('name'));
            $page->save();
            header('content-type: application/json');
            die(json_encode(array('status' => 'ok')));
        }
    }

    public function clearcacheAction()
    {
        Auth::mustHave('cms.update');
        foreach(Page::find() as $page)
        {
            $page->deleteAllCache();
        }
        header('content-type: application/json');
        die(json_encode(array('status' => 'ok')));
    }
    
    public function savepageAction()
    {
        Auth::mustHave('cms.update');
        $t = new \shozu\Taconite();
        $page = Page::findOne('id = ?', array($_POST['id']));
        if(!$page)
        {
           $t->alert('No such page.')->render(true);
        }
        try
        {
            $page->setBody($_POST['page_body']);
            $page->setHeading($_POST['heading']);
            $page->setLayout($_POST['layout']);
            $page->setTemplate($_POST['template']);
            $page->setUrl($_POST['url']);
            $page->setPublished(false);
            if(isset($_POST['published']) && Auth::hasAccessTo('cms.publish'))
            {
                $page->setPublished(true);
            }
            $page->setAllow_comments(isset($_POST['allow_comments']));
            $page->setPublished_from($_POST['published_from']);
            $page->setPublished_to($_POST['published_to']);
            $page->setSeo_description($_POST['seo_description']);
            $page->setSeo_keywords($_POST['seo_keywords']);
            $page->setSeo_title($_POST['seo_title']);
            $page->setAuthor($_POST['author']);
            try
            {
                Page::beginTransaction();
                if(isset($_POST['sync_publishing']))
                {
                    $page->synchronizeDescendantsPublishing();
                }
                $page->save();
                Page::commit();
            }
            catch(Exception $e)
            {
                Page::rollBack();
                $t->alert($v->T('Something bad happened :-('))->render(true);
            }
            $v = new \shozu\View(__FILE__);
            $t->alert($v->T('Page saved :-)'))->render(true);
        }
        catch(\Exception $e)
        {
            $t->alert($e->getMessage())->render(true);
        }
    }

    public function jsontreeAction()
    {
        Auth::mustHave('cms.read');
        $ret = array();
        $children = array();
        $node_id = str_replace('node_', '', $this->getParam('id', 0));
        if($node_id == 0)
        {
            $children[] = Page::fetchRoot();
        }
        else
        {
            $page = Page::findOne('id = ?', array($node_id));
            if($page)
            {
                $children = $page->getChildren();
            }
        }
        foreach($children as $child)
        {
            $ret[] = array(
                'data' => array(
                    'title' => $child->getTitle(),
                ),
                'attr' => array(
                    'id' => 'node_'.$child->getId()
                ),
                'state'=> count($child->getChildren()) ? 'closed' : 'leaf',
            );
        }
        header('content-type: application/json');
        die(json_encode($ret));
    }

    public function resourcesAction()
    {
        Auth::mustHave('cms.manage_resources');
        $s = \shozu\Shozu::getInstance();

        $templates = glob(__DIR__ . '/../views/' . \DAIZU_THEME . '/templates/*.php');
        $layouts = glob(__DIR__ . '/../views/' . \DAIZU_THEME . '/layouts/*.php');
        $this->setLayout('admin');
        $this->assignToLayout('js_includes', array(
            $s->base_url . 'static/jquery/jquery-ui-1.7.2.custom.min.js',
            $s->base_url . 'static/elfinder.full.js',
            $s->url('cms/admin/elfinderstrings')
        ));
        $this->assignToLayout('css_includes', array(
            $s->base_url . 'static/jquery/ui-themes/base/ui.all.css',
            $s->base_url . 'static/elfinder.css'
        ));
        $this->display('edit_resources');
    }

    public function usersAction()
    {
        Auth::mustHave('cms.manage_users');

        $this->setLayout('admin');
        $this->display('edit_users');
    }

    public function elfinderconnectorAction()
    {
        Auth::mustHave('cms.manage_resources');
        $s = \shozu\Shozu::getInstance();
        $opts = array(
            'root'            => $s->document_root,                       // path to root directory
            'URL'             => $s->base_url, // root directory URL
            'rootAlias'       => 'document root',       // display this instead of root directory name
             'fileMode'     => 0666,         // new files mode
             'dirMode'      => 0777,         // new folders mode
             'imgLib'       => 'auto',       // image manipulation library (imagick, mogrify, gd)

        );
        $fm = new \elFinder($opts);
        $fm->run();
    }

    public function elfinderstringsAction()
    {
        header('content-type: text/javascript');
        $this->display('elstrings');
    }
}
