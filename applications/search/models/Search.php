<?php
namespace search\models;
class Search
{
    private $words_ids = array();
    private $type = 'and';
    private $collection_id = null;
    private $field_id = null;
    private $results = array();
    private $has_been_executed = false;

    /**
     *
     * @param string $text
     * @param Analyzer $analyzer
     */
    public function analyzeText($text, Analyzer $analyzer = null)
    {
        if(is_null($analyzer))
        {
            $analyzer = StandardAnalyzer::getInstance();
        }
        foreach($analyzer->analyze($text) as $occurence)
        {
            $this->words_ids[] = $occurence->getWord_id();
        }
        $this->words_ids = array_unique($this->words_ids);
        return $this;
    }

    public function getAnalyzedWords()
    {
        if(count($this->words_ids))
        {
            return Word::find('id in('.implode(',', $this->words_ids).')');
        }
        return array();
    }

    /**
     *
     * @param Collection $collection
     */
    public function limitToCollection(Collection $collection)
    {
        $this->collection_id = $collection->getId();
        return $this;
    }

    /**
     *
     * @param Field $field
     */
    public function limitToField(Field $field)
    {
        $this->field_id = $field->getId();
        return $this;
    }

    public function setType($type = 'and')
    {
        if(!in_array($type, array('and', 'or')))
        {
            throw new \InvalidArgumentException('search type must be "and" or "or"');
        }
        $this->type = $type;
        return $this;
    }

    public function execute()
    {
        if(!count($this->words_ids))
        {
            return array();
        }
        $collectionCondition = '';
        if(!empty($this->collection_id))
        {
            $collectionCondition = ' AND collection_id = ' . (int)$this->collection_id;
        }
        $fieldCondition = '';
        if(!empty($this->field_id))
        {
            $fieldCondition = ' AND field_id = ' . (int)$this->field_id;
        }
        $conditions = array();
        foreach($this->words_ids as $word_id)
        {
            $conditions[] = 'document_id IN (SELECT document_id FROM ' . Occurence::beanName() . ' WHERE word_id = ' . $word_id . ')';
        }
        if($this->type == 'and')
        {
            $query = '
            SUM(weight) AS score, document_id FROM ' . Occurence::beanName() . '
            WHERE ' . implode("\nAND ", $conditions) . '
            AND word_id IN (' . implode(',', $this->words_ids) . ')' . $collectionCondition . $fieldCondition .'
            GROUP BY document_id ORDER BY score DESC';
        }
        else
        {
            $query = '
            SUM(weight) AS score, document_id FROM ' . Occurence::beanName() . '
            WHERE id IN (SELECT distinct(id) FROM ' . Occurence::beanName() . ' WHERE word_id IN (' . implode(',', $this->words_ids) . '))' . $collectionCondition . $fieldCondition . '
            GROUP BY document_id ORDER BY score DESC';
        }
        //@todo: cache this
        $this->results = Occurence::getAdapter()->get('SELECT '. $query);
        $this->has_been_executed = true;
        return $this;
    }

    public function count()
    {
        return count($this->results);
    }

    /**
     *
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    public function getDocuments($offset = 0, $length = 50)
    {
        if(!$this->has_been_executed)
        {
            $this->execute();
        }
        $offset = abs((int)$offset);
        $length = abs((int)$length);

        $documents = array();
        foreach(array_slice($this->results, $offset, $length) as $result)
        {
            $document = Document::findOne('id = ?', array($result['document_id']));
            if($document)
            {
                $documents[] = $document;
            }
        }
        return $documents;
    }
}