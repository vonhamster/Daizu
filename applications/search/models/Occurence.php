<?php
namespace search\models;
class Occurence extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->isStampable = false;
        $this->addColumn('word_id', array(
            'type' => 'integer'
        ));
        $this->addColumn('collection_id', array(
            'type' => 'integer'
        ));
        $this->addColumn('document_id', array(
            'type' => 'integer'
        ));
        $this->addColumn('field_id', array(
            'type' => 'integer'
        ));
        $this->addColumn('weight', array(
            'type'    => 'integer',
            'default' => 10
        ));
    }

    public function setWord(Word $word)
    {
        $this->setWord_id($word->id);
    }
    public function setCollection(Collection $collection)
    {
        $this->setCollection_id($collection->id);
    }
    public function setDocument(Document $document)
    {
        $this->setDocument_id($document->id);
    }
    public function setField(Field $field)
    {
        $this->setField_id($field->id);
    }
}