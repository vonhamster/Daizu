<?php
namespace search\models;
class Hooks
{
    public static function Page(\cms\models\Page $page)
    {
        $analyzer = StandardAnalyzer::getInstance();
        if($page->getAnalyzer() == 'french')
        {
            $analyzer = FrenchAnalyzer::getInstance();
        }
        $document = Document::findOne('identifier = ?', array('page.' . $page->getId()));
        if(!$document)
        {
            $document = new Document;
        }
        $document
            ->setIdentifier('page.'.$page->getId())
            ->setCollection(Collection::fetch('cms page index'))
            ->setMeta('display_title', $page->getTitle())
            ->setMeta('id', $page->getId())
            ->setMeta('class', get_class($page))
            ->setMeta('url', $page->getUrl())
            ->addField('body', $page->getBody(), $analyzer)
            ->addField('title', $page->getTitle(), $analyzer)
            ->addField('keywords', $page->getSeo_keywords(), $analyzer)
            ->addToIndex();
    }
}