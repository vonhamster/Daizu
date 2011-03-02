<?php
use \search\models\Document as Document;

// add document to index

$document = new Document;
$document
        ->setIdentifier('product.123456')
        ->setCollection(Collection::fetch('produits'))
        ->setMeta('display_title', 'Marteau fubar, le marteau des champions.')
        ->setMeta('url', '/marteau-fubar.html')
        ->addField('title', 'marteau fubar', StandardAnalyzer::getInstance())
        ->addField('description','Le marteau FuBar est quatre outils en un : marteau, pince à décoffrer, arrache-clous et pied de biche.')
        ->addToIndex(new Index);

// remove document

$document = Document::findOne('identifier = ?', array('product.123456'));

if($document)
{
    $document->delete(); // index is cleaned up here
}