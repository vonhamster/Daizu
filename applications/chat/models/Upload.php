<?php
namespace chat\models;
class Upload extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->addColumn('hash',array(
            'unique' => true
        ));
        $this->addColumn('name');
        $this->addColumn('mime');
    }
}