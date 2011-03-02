<?php
namespace search\models;
use \search\models\Collection as Collection;
class Document extends \shozu\ActiveBean
{
    private $fields;
    protected function setTableDefinition()
    {
        $this->addColumn('identifier', array(
            'unique' => true
        ));
        $this->addColumn('metas', array(
            'type' => 'array'
        ));
        $this->addColumn('collection_id', array(
            'type' => 'integer'
        ));
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     *
     * @param Collection $collection 
     * @return Document
     */
    public function setCollection(Collection $collection)
    {
        $this->setCollection_id($collection->id);
        return $this;
    }

    public function getCollection()
    {
        $id = $this->getCollection_id();
        if($id)
        {
            return Collection::findOne('id = ?', array($id));
        }
        return false;
    }


    /**
     *
     * @param string $meta_name
     * @return mixed
     */
    public function getMeta($meta_name)
    {
        $metas = $this->getMetas();
        foreach($metas as $name => $value)
        {
            if($name == $meta_name)
            {
                return $value;
            }
        }
    }

    /**
     *
     * @param string $meta_name
     * @param mixed $meta_value
     * @return Document
     */
    public function setMeta($meta_name, $meta_value)
    {
        $metas = $this->getMetas();
        $metas[$meta_name] = $meta_value;
        $this->setMetas($metas);
        return $this;
    }

    /**
     *
     * @param string $meta_name
     * @return Document
     */
    public function removeMeta($meta_name)
    {
        $metas = $this->getMetas();
        foreach($metas as $name => $value)
        {
            if($name == $meta_name)
            {
                unset($metas[$name]);
            }
        }
        $this->setMetas($metas);
        return $this;
    }

    /**
     *
     * @param string $field_name
     * @param string $field_value
     * @param Analyzer $analyzer
     * @return Document
     */
    public function addField($field_name, $field_value, Analyzer $analyzer = null)
    {
        if(is_null($analyzer))
        {
            $analyzer = StandardAnalyzer::getInstance();
        }
        $this->fields[$field_name] = array($field_value, $analyzer);
        return $this;
    }

    /**
     *
     * @param Index $index
     * @return Document
     */
    public function addToIndex(Index $index = null)
    {
        if(is_null($index))
        {
            $index = new Index;
        }
        if(!count($this->fields))
        {
            throw new Exception('Nothing to index. use Document::addField()');
        }
        $identifier = $this->getIdentifier();
        if(empty($identifier))
        {
            throw new Exception('You must give an identifier to your document');
        }
        $this->save();
        $this->removeOccurences();
        foreach($this->fields as $field_name => $data)
        {
            $occurences = $data[1]->analyze($data[0]);
            foreach($occurences as $key => $occurence)
            {
                $occurence->setDocument($this);
                $occurence->setCollection_id($this->getCollection_id());
                $occurence->setField(Field::fetch($field_name));
                $index->addOccurence($occurence);
            }
        }
        $this->fields = null;
        return $this;
    }

    
    private function removeOccurences()
    {
        try
        {
            Occurence::getAdapter()->exec('delete from '. Occurence::beanName() . ' where document_id = ' . (int)$this->getId());
        }
        catch(\RedBean_Exception_SQL $e)
        {
            /*
            if(!strstr($e->getMessage(), 'no such table') || !strstr($e->getMessage(), 'base or table view not found'))
            {
                throw $e;
            }*/
        }
    }

    public function delete()
    {
        $this->removeOccurences();
        parent::delete();
    }
}