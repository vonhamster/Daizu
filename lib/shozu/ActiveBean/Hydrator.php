<?php
namespace shozu\ActiveBean;
class Hydrator implements \Iterator, \Countable
{
    private $class_to_hydrate;
    private $columns = array();
    private $rows = array();

    public function  __construct(\PDO $db, $class_to_hydrate, $select_statement, array $replace_values = null)
    {
        $this->class_to_hydrate = $class_to_hydrate;
        $this->execute($db, $select_statement, $replace_values);
    }

    private function execute(\PDO $db, $select_statement, array $replace_values = array())
    {
        if(strtolower(substr($select_statement, 0, 7)) != 'select ')
        {
            $select_statement = 'select * from ' . \shozu\ActiveBean::beanName($this->class_to_hydrate) . ' where ' . $select_statement;
        }
        try
        {
            if(count($replace_values))
            {
                $set = $db->prepare($select_statement);
                $set->execute($replace_values);
            }
            else
            {
                $set = $db->query($select_statement);
            }
            $got_columns = false;
            while(($row = $set->fetch(\PDO::FETCH_ASSOC)) !== false)
            {
                if(!$got_columns)
                {
                    $this->columns = array_keys($row);
                    $got_columns = true;
                }
                $this->rows[] = array_values($row);
            }
            return;
        }
        catch(\PDOException $e)
        {
            if(in_array($e->getCode(), array('42P01', '42703', '42S02', '42S22'))
                || strpos($e->getMessage(), 'no such table'))
            {
                $this->rows = array();
                return;
            }
            throw $e;
        }
    }

    public function count()
    {
        return count($this->rows);
    }

    public function rewind()
    {
        reset($this->rows);
    }

    public function current()
    {
        $row = current($this->rows);
        if(is_null($row))
        {
            return $row;
        }
        return new $this->class_to_hydrate(array_combine($this->columns, $row));
    }

    public function key()
    {
        return key($this->rows);
    }

    public function next()
    {
        $row = next($this->rows);
        if($row === false)
        {
            return null;
        }
        return new $this->class_to_hydrate(array_combine($this->columns, $row));
    }

    public function valid()
    {
        if(!is_null($this->key()))
        {
            return true;
        }
        return false;
    }
}