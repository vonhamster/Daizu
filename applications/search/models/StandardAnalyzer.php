<?php
namespace search\models;
class StandardAnalyzer implements Analyzer
{
    private static $instance;
    private function __construct()
    {

    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function analyze($text)
    {
        $text = \shozu\Inflector::removeDiacritics($text);
        $text = strip_tags($text);
        $text = strtolower($text);
        $text = str_replace(array('-','\'') , array(' ',' ') , $text);
        $text = preg_replace('/[\'`´"]/', '', $text);
        $text = preg_replace('/[^a-z0-9]/', ' ', $text);
        $text = str_replace('  ', ' ', $text);
        $terms = explode(' ', $text);
        $occurences = array();
        foreach($terms as $term)
        {
            $term = trim($term);
            if (empty($term))
            {
                continue;
            }
            $occurence = new Occurence;
            $occurence->setWord(Word::fetch($term));
            $occurence->setWeight(10);
            $occurences[] = $occurence;
        }
        return $occurences;
    }
}