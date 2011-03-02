<?php
namespace cms\models;
use \cms\models\Page as Page;

class Comment extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->addColumn('name',array(
            'length' => 64,
            'formatters' => array('trim','limiter'),
            'validators' => array('notblank')
        ));
        $this->addColumn('email', array(
            'length' => 64,
            'formatters' => array('trim','limiter'),
            'validators' => array('notblank', 'email')
        ));
        $this->addColumn('url', array(
            'length' => 128,
            'formatters' => array('trim','limiter')
        ));
        $this->addColumn('content', array(
            'formatters' => array('trim'),
            'validators' => array('notblank')
        ));
        $this->addColumn('published', array(
            'type' => 'boolean',
            'default' => '0'
        ));
        $this->addColumn('page_id', array(
            'validators' => array('notblank')
        ));
    }

    public function isPublished()
    {
        return (bool)$this->getPublished();
    }

    public static function findCommentsForPage(Page $page)
    {
        return self::find('page_id = ? order by created_at asc, id asc', array($page->id));        
    }

    public static function findPublishedCommentsForPage(Page $page)
    {
        return self::find('page_id = ? and published = 1 order by created_at asc, id asc', array($page->id));
    }

    protected function preSave()
    {
        \shozu\Observer::notify('daizu.comment.presave', $this);
    }

    protected function postSave()
    {
        \shozu\Observer::notify('daizu.comment.postsave', $this);
    }

    public static function notify(Comment $comment, Page $page)
    {
        $page->deleteAllCache();
        if(\DAIZU_SEND_NOTIFICATIONS)
        {
            $subject = 'daizu comment';
            $content = 'By ' . $comment->getName() . ' ('.$comment->getEmail().') on page "' . $page->getTitle() . '"' . ":\n\n";
            $content.= $comment->getContent();
            mail(\DAIZU_NOTIFY_EMAIL, $subject, $content);
        }
    }
}