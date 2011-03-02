<?php
namespace search\models;

class Meta extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->addColumn('document_id', array(
            'type' => 'integer'
        ));
        $this->addColumn('meta_name', array(
            'type' => 'string'
        ));
        $this->addColumn('meta_value', array(
            'type' => 'string'
        ));
    }
}