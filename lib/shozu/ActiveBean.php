<?php
namespace shozu;
use \shozu\ActiveBean\Hydrator as Hydrator;
/**
 * ActiveRecord-like layer on top of RedBean.
 *
 * Encapsulate a bean and adds data validation and format.
 */
abstract class ActiveBean implements \Serializable
{
    const SQL_UNKNOWN_TABLE  = '42S02';
    const SQL_UNKNOWN_COLUMN = '42S22';
    private static $toolbox;
    private static $redbean;
    private static $association;
    private $bean;
    private $lastError;
    private $validation_errors;
    protected $isStampable = true;
    protected $linkop = array();
    protected $columns;

    /**
     * Construct new Record
     *
     * @param mixed $bean bean or bean id or array
     */
    public function __construct($bean = null)
    {
        $this->setTableDefinition();
        if($this->isStampable)
        {
            $this->addColumn('created_at', array(
                'type' => 'datetime'
            ));
            $this->addColumn('modified_at', array(
                'type' => 'datetime'
            ));
        }
        if(is_null($bean))
        {
            $this->bean = self::redbean()->dispense(self::beanName());
        }
        elseif(is_int($bean))
        {
            $this->bean = self::redbean()->load(self::beanName(), $bean);
        }
        elseif($bean instanceof \RedBean_OODBBean)
        {
            $this->bean = $bean;
        }
        elseif(is_array($bean))
        {
            $this->bean = self::redbean()->dispense(self::beanName());
            $this->fromArray($bean);
        }
        else
        {
            throw new \InvalidArgumentException('Unsupported bean type');
        }
        $this->addUniqueIndices();
        $this->setDefaultValues();
    }

    private function addUniqueIndices()
    {
        $colNames = array();
        foreach($this->columns as $col)
        {
            if($col['unique'])
            {
                $colNames[] = $col['name'];
            }
        }
        if(count($colNames) > 0)
        {
            $this->bean->setMeta("buildcommand.unique.0", $colNames);
        }
    }

    private function setDefaultValues()
    {
        foreach($this->columns as $col)
        {
            if(!is_null($col['default']) && is_null($this->bean->$col['name']))
            {
                $this->bean->$col['name'] = $col['default'];
            }
        }
    }

    /**
     * Get RedBean's toolbox
     *
     * @return \RedBean_ToolBox
     */
    final public static function toolbox()
    {
        if(is_null(self::$toolbox))
        {
            self::$toolbox = \RedBean_Setup::getToolBox();
        }
        return self::$toolbox;
    }

    /**
     * Get RedBean's OODB
     *
     * @return \RedBean_OODB
     */
    final public static function redbean()
    {
        if(is_null(self::$redbean))
        {
            self::$redbean = self::toolbox()->getRedBean();
        }
        return self::$redbean;
    }

    /**
     * Get RedBean's association manager
     *
     * @return \RedBean_AssociationManager
     */
    final public static function association()
    {
        if(is_null(self::$association))
        {
            self::$association = new \RedBean_AssociationManager(self::toolbox());
        }
        return self::$association;
    }

    /**
     * Any class that extends ActiveBean must define this
     */
    abstract protected function setTableDefinition();
    /**
     * Add a column to the model definition.
     *
     * <code>
     * $this->addColumn('email', array(
     *     'unique' => true,
     *     'validators' => array('notblank','email'),
     *     'formatters' => array('trim','lowercase')
     * ));
     * </code>
     *
     * @param string $name column name
     * @param array $config column configuration
     */
    final protected function addColumn($name, array $config = null)
    {
        $name = (string)$name;
        $type = isset($config['type']) ? $config['type'] : 'string';
        $length = isset($config['length']) ? $config['length'] : null;
        $this->columns[$name] = array(
            'name'       => $name,
            'type'       => $type,
            'verbose'    => isset($config['verbose'])    ? $config['verbose']              : null,
            'help'       => isset($config['help'])       ? $config['help']                 : null,
            'length'     => $length,
            'formatters' => isset($config['formatters']) ? (array)$config['formatters']    : array(),
            'validators' => isset($config['validators']) ? (array)$config['validators']    : array(),
            'default'    => isset($config['default'])    ? $config['default']              : null,
            'unique'     => isset($config['unique'])     ? (bool)$config['unique']         : false,
            'references' => isset($config['references']) ? $config['references']           : null,
            'primary'    => isset($config['primary'])    ? (bool)$config['primary']        : false,
            'autoinc'    => isset($config['autoinc'])    ? (bool)$config['autoinc']        : false,
            'notnull'    => isset($config['notnull'])    ? (bool)$config['notnull']        : false,
            'collate'    => isset($config['collate'])    ? $config['collate']              : 'utf8_unicode_ci',
            'ondelete'   => isset($config['ondelete'])   ? $config['ondelete']             : null
        );
    }

    /**
     * Magic setter
     */
    final public function __set($key, $value)
    {
        if(isset($this->columns[$key]))
        {
            if(!empty($this->columns[$key]['formatters']))
            {
                foreach($this->columns[$key]['formatters'] as $formatter)
                {
                    if($formatter == 'limiter' && !empty($this->columns[$key]['length']))
                    {
                        $value = mb_substr($value, 0, $this->columns[$key]['length'], 'UTF-8');
                    }
                    elseif(is_array($formatter))
                    {
                        $callable = $formatter[0] . '::' . $formatter[1];
                        if(!method_exists($validator[0], $validator[1]))
                        {
                            throw new \Exception($formatter . ' formatter not defined');
                        }
                        $value = $callable($value);
                    }
                    elseif(method_exists($this, $formatter.'Formatter'))
                    {
                        $value = $this->{$formatter.'Formatter'}($value);
                    }
                    else
                    {
                        throw new \Exception($formatter . ' formatter not defined');
                    }
                }
            }
            switch($this->columns[$key]['type'])
            {
                case 'int':
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'bool':
                case 'boolean':
                    $value = $value ? 1 : 0;
                    break;
                case 'array':
                    if(!is_string($value))
                    {
                        $value = json_encode($value);
                    }
                    break;
                case 'object':
                    if(!is_string($value))
                    {
                        $value = serialize($value);
                    }
                    break;
                case 'datetime':
                case 'time':
                    if(is_integer($value))
                    {
                        $value = date('Y-m-d H:i:s', $value);
                    }
                    elseif($value instanceof \DateTime)
                    {
                        $value = $value->format('Y-m-d H:i:s');
                    }
                default:
                    $value = $value;
                    break;
            }

            if(!empty($this->columns[$key]['length']) &&
               mb_strlen($value, 'UTF-8') > $this->columns[$key]['length'])
            {
                throw new \Exception('value exceeds length limit for ' . $key);
            }
        }
        // pack hex to binary
        if($key == 'id' && strlen($value) > 16 && self::toolbox()->getWriter() instanceof \RedBean_QueryWriter_MySQLUUID)
        {
            $value = pack("h*", $value);
        }
        $this->bean->$key = $value;
    }



    /**
     * Magic getter
     */
    final public function __get($key)
    {
        if(!is_null($this->bean->$key) && $key != 'id')
        {
            if($this->columns[$key]['type'] == 'bool' ||
               $this->columns[$key]['type'] == 'boolean')
            {
                return (bool)$this->bean->$key;
            }
            if($this->columns[$key]['type'] == 'datetime' ||
               $this->columns[$key]['type'] == 'time')
            {
                return new \DateTime($this->bean->$key);
            }
            if($this->columns[$key]['type'] == 'array')
            {
                return json_decode($this->bean->$key, true);
            }
            if($this->columns[$key]['type'] == 'object')
            {
                return unserialize($this->bean->$key);
            }
            return $this->bean->$key;
        }
        if($key == 'id' && self::toolbox()->getWriter() instanceof \RedBean_QueryWriter_MySQLUUID)
        {
            return bin2hex($this->bean->$key);
        }
        return $this->bean->$key;
    }

    /**
     * setXxx and getXxx
     */
    final public function __call($name, $args)
    {
        if(substr($name, 0, 3) == 'set')
        {
            $prop = strtolower(substr($name, 3, 1)) . substr($name, 4);
            $this->__set($prop, $args[0]);
        }
        elseif(substr($name, 0, 3) == 'get')
        {
            $prop = strtolower(substr($name, 3, 1)) . substr($name, 4);
            return $this->__get($prop);
        }
        else
        {
            throw new \BadMethodCallException('no such method: ' . $name);
        }
    }

    /**
     * Find ActiveBeans
     *
     * @param string $sql
     * @param array $args
     * @return \shozu\ActiveBean\Hydrator
     */
    public static function find($sql = '1', array $args = array())
    {
        return new Hydrator(self::getPDO(), get_called_class(), $sql, $args);
    }

    /**
     * Find ActiveBean
     *
     * @param string $sql
     * @param array $args
     * @return ActiveBean
     */
    public static function findOne($sql = '1', array $args = array())
    {
        $sql .= ' LIMIT 1';
        $active_beans = self::find($sql, $args);
        foreach($active_beans as $active_bean)
        {
            return $active_bean;
        }
        return false;
    }

    /**
     * Count number of instances
     *
     * @param string $where
     * @param array $args
     * @return integer
     * @todo optimize
     */
    public static function count($where = '1', array $args = array())
    {
        return count(self::find($where, $args));
    }

    /**
     * Exports ActiveBean to an array
     *
     * @return array
     */
    public function toArray()
    {
        $ret = array('id'=> $this->__get('id'));
        foreach($this->columns as $col)
        {
            $ret[$col['name']] = $this->__get($col['name']);
        }
        return $ret;
    }

    /**
     * Import from an array
     *
     * @param array $a
     */
    public function fromArray(array $a)
    {
        foreach($a as $k => $v)
        {
            $call = 'set' . ucfirst($k);
            $this->$call($v);
        }
    }

    /**
     * Link another ActiveBean to this.
     *
     * /!\ Operation takes effect on save.
     *
     * @param ActiveBean $ActiveBean
     */
    public function link(ActiveBean $record)
    {
        $this->linkop[] = array('link', $record);
    }

    /**
     * Unlink another ActiveBean from this or Unlink all ActiveBeans of given
     * class.
     *
     * /!\ Operation takes effect on save.
     *
     * @param mixed $record_or_class
     */
    public function unlink($record_or_class)
    {
        $this->linkop[] = array('unlink', $record_or_class);
    }

    /**
     * Get related ActiveBeans of given class
     *
     * @param string $class
     * @param boolean $hydrate
     * @return array
     */
    final public function getRelated($class, $hydrate = true)
    {
        if(is_callable(array($class, 'beanName')))
        {
            $beanName = $class::beanName();
            $keys = self::association()->related($this->bean, $beanName);
            if($hydrate)
            {
                $fullObjects = array();
                $beans = self::redbean()->batch($beanName, $keys);
                foreach($beans as $bean)
                {
                    $fullObjects[] = new $class($bean);
                }
                return $fullObjects;
            }
            return $keys;
        }
        return array();
    }

    /**
     * Get last validation error
     *
     * @return string
     */
    final public function lastError()
    {
        return $this->lastError;
    }

    final public function errors()
    {
        return $this->validation_errors;
    }

    /**
     * Get bean
     *
     * @return \RedBean_OODBBean
     */
    final public function getBean()
    {
        return $this->bean;
    }

    /**
     * Save ActiveBean to database
     */
    public function save()
    {
        if(method_exists($this, 'preSave'))
        {
            $this->preSave();
        }

        if($this->_validates())
        {
            if($this->isStampable)
            {
                $now = time();
                if(is_null($this->created_at))
                {
                    $this->created_at = $now;
                }
                $this->modified_at = $now;
            }
            self::redbean()->store($this->bean);
            foreach($this->linkop as $relation)
            {
                if($relation[0] == 'link')
                {
                    self::association()->associate($this->bean, $relation[1]->getBean());
                    \RedBean_Plugin_Constraint::addConstraint($this->bean, $relation[1]->getBean());
                }
                if($relation[0] == 'unlink')
                {
                    if($relation[1] instanceof ActiveBean)
                    {
                        self::association()->unassociate($this->bean, $relation[1]->getBean());
                    }
                    else
                    {
                        if(is_callable(array($relation[1], 'beanName')))
                        {
                            self::association()->clearRelations($this->bean, $relation[1]::beanName());
                        }
                    }
                }
            }
        }
        else
        {
            throw new \Exception('Record validation error. ' . $this->lastError);
        }

        if(method_exists($this, 'postSave'))
        {
            $this->postSave();
        }
    }

    /**
     * Delete ActiveBean from database
     */
    public function delete()
    {
        if(method_exists($this, 'preDelete'))
        {
            $this->preDelete();
        }
        self::redbean()->trash($this->bean);
        if(method_exists($this, 'postDelete'))
        {
            $this->postDelete();
        }
    }

    /**
     * Get bean name for this ActiveBean
     *
     * @return string
     */
    final public static function beanName($class = null)
    {
        if(is_null($class))
        {
            $class = get_called_class();
        }
        $chop = explode('\\', $class);
        foreach($chop as $k => $piece)
        {
            if($piece == 'models')
            {
                unset($chop[$k]);
            }
        }
        return strtolower(implode('', $chop));
    }

    /**
     * Get database adapter
     *
     * @return \RedBean_DBAdapter
     */
    final public static function getAdapter()
    {
        return self::toolbox()->getDatabaseAdapter();
    }

    /**
     * Get PDO instance where available
     *
     * @return mixed \PDO or false
     */
    final public static function getPDO()
    {
        $db_driver = self::toolbox()->getDatabaseAdapter()->getDatabase();
        if($db_driver instanceof \RedBean_Driver_PDO)
        {
            return $db_driver->getPDO();
        }
        return false;
    }

    protected function trimFormatter($string)
    {
        return trim($string);
    }

    protected function uppercaseFormatter($string)
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    protected function lowercaseFormatter($string)
    {
        return mb_strtolower($string, 'UTF-8');
    }

    protected function nodiacriticsFormatter($utfString)
    {
        return strtr(utf8_decode($utfString),
                     utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ'),
                     'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn');
    }

    protected function emailValidator($string)
    {
        if(function_exists('filter_var'))
        {
            if(filter_var($string, FILTER_VALIDATE_EMAIL))
            {
                return true;
            }
            return false;
        }
        return preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $string);
    }

    public function isValid()
    {
        return $this->_validates();
    }

    protected function _validates()
    {
        $validates = true;
        $this->validation_errors = array();
        foreach($this->columns as $fieldName => $description)
        {
            if(empty($description['validators']))
            {
                continue;
            }
            if(empty($this->bean->$fieldName) and
                !in_array('notblank', $description['validators']))
            {
                return true;
            }
            if(empty($this->bean->$fieldName) and
                in_array('notblank', $description['validators']))
            {
                $this->lastError = (!is_null($this->columns[$fieldName]['verbose']) ? $this->columns[$fieldName]['verbose'] : $fieldName) . ': notblank';
                $this->validation_errors[$fieldName][] = 'not blank';
                $validates = false;
            }
            foreach($description['validators'] as $validator)
            {
                if($validator === 'notblank')
                {
                    continue;
                }
                if(is_array($validator))
                {
                    $callable = $validator[0] . '::' . $validator[1];
                    if(!method_exists($validator[0], $validator[1]))
                    {
                        throw new \Exception($callable . ' is undefined.');
                    }
                    $res = (bool)$callable($this->bean->$fieldName, $description);
                }
                else
                {
                    $callable = $validator.'Validator';
                    if(!method_exists($this, $callable))
                    {
                        throw new \Exception($callable . ' is undefined.');
                    }
                    $res = (bool)$this->$callable($this->bean->$fieldName, $description);
                }
                if(!$res)
                {
                    $this->lastError = (!is_null($this->columns[$fieldName]['verbose']) ? $this->columns[$fieldName]['verbose'] : $fieldName) . ': ' . $validator;
                    $this->validation_errors[$fieldName][] = (string)$callable;
                    $validates = false;
                }
            }
        }
        return $validates;
    }

    /**
     * Transaction begin shortcut
     */
    final public static function beginTransaction()
    {
        self::getAdapter()->startTransaction();
    }
    /**
     * Transaction commit shortcut
     */
    final public static function commit()
    {
        self::getAdapter()->commit();
    }
    /**
     * Transaction roll back shortcut
     */
    final public static function rollBack()
    {
        self::getAdapter()->rollback();
    }


    public function serialize()
    {
        return serialize($this->toArray());
    }

    public function unserialize($serialized)
    {
        $this->__construct(unserialize($serialized));
    }
}

