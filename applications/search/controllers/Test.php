<?php
namespace search\controllers;

use \search\models\Document as Document;
use \search\models\Collection as Collection;
use \search\models\Occurence as Occurence;
use \search\models\Word as Word;
use \search\models\Index as Index;
use \search\models\Search as Search;
use \search\models\StandardAnalyzer as StandardAnalyzer;
use \search\models\FrenchAnalyzer as FrenchAnalyzer;

if(!defined('DAIZU_TEST'))
{
    die('Not in test env');
}

class Test extends \shozu\Controller\Testing
{
    public function testCreateDocument()
    {
        try
        {
            // create document
            $document = new Document;
            $document
                    ->setIdentifier(uniqid()) // the identifier should be created by your application
                    ->setCollection(Collection::fetch('my cd wishlist'))
                    ->setMeta('display_title', 'Bill Evans - Waltz for Debby')
                    ->setMeta('url', 'http://www.amazon.fr/Waltz-Debby-Bill-Trio-Evans/dp/B000000YBQ')
                    ->addField('artist', 'Bill Evans')
                    ->addField('title', 'Waltz for Debby')
                    ->addField('genre', 'Jazz')
                    ->addToIndex();
            
            $document = Document::findOne('identifier = ?', array('product.123456'));
            $this->asrt($document->getMeta('url'),'http://www.bricozor.com/marteau-fubar.html');
        }
        catch(\Exception $e)
        {
            $this->fail($e->getMessage());
        }
    }

    public function testSearchIndex()
    {
        $search = new Search;
        $documents = $search
                        ->setType('and')
                        ->limitToCollection(Collection::fetch('my cd wishlist'))
                        ->analyzeText('bill evans')
                        ->getDocuments(0, 20);
        $this->asrt(count($documents), 1);
    }
}