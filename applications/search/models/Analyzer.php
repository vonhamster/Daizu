<?php
namespace search\models;
/**
 * The analyzer extract Occurence instances from text input
 */
interface Analyzer
{
    /**
     * 
     * @param string $text text should be utf-8
     * @return array of Occurence
     */
    public function analyze($text);
}
