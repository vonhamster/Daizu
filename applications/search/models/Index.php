<?php
namespace search\models;
class Index
{
    public function addOccurence(Occurence $occurence)
    {
        $occurence->save();
    }
}