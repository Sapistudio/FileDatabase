<?php
namespace SapiStudio\FileDatabase;

class Handler implements \IteratorAggregate, \Countable{

    protected $databaseConfig   = null;
    protected $databaseData     = null;
    protected $documentEntries  = [];
    protected $documentfilters  = [];
    protected $databasename     = null;
    protected $tableFields      = null;
    protected static $uniqueIdentifier = 'id';
    protected $currentId;
    protected $currentKey;

    public static function load($databaseName,array $options = [])
    {
        if (!self::dbExists($databaseName))
            throw new \Exception('Database: '.$databaseName.' doesn\'t exists.');
        return new static($databaseName,$options);
    }
    
    public static function createDatabase($databaseName, array $fields = [])
    {
        $fields = Validate::arrToLower($fields);
        if (self::dbExists($databaseName))
            throw new \Exception($databaseName . '" already exists');
        $types = array_values($fields);
        Validate::types($types);
        if (!array_key_exists(self::getIdentifier(), $fields))
            $fields = [self::getIdentifier() => 'integer'] + $fields;
        $data            = new \stdClass();
        $data->last_id   = 0;
        $data->schema    = $fields;
        Document::load($databaseName)->put([]);
        Document::loadConfig($databaseName)->put($data);
    }
    
    public static function dbExists($databaseName){
        return (Document::load($databaseName)->exists() && Document::loadConfig($databaseName)->exists()) ? true : false;
    }
    
    public function __construct($databaseName = null){
        $this->databasename     = $databaseName;
        $this->databaseData     = Document::load($this->getName());
        $this->databaseConfig   = Document::loadConfig($this->getName());
        return $this->setFields();
    }
    
    public function __set($name, $value)
    {
        if ($this->checkField($name) && $this->checkType($name,$value))
            $this->tableFields->{$name} = utf8_encode($value);
    }

    public function __get($name)
    {
        return (isset($this->tableFields->{$name})) ? $this->tableFields->{$name} : false;
    }

    public function __isset($name)
    {
        return isset($this->tableFields->{$name});
    }
    
    protected function getData()
    {
        return $this->databaseData->get();
    }

    protected function setData()
    {
        $this->documentEntries = $this->getData();
    }
    
    protected function filterEntries()
    {
        $this->setData();
        foreach ($this->documentfilters as $func => $args)
        {
            if (!empty($args))
                call_user_func([$this, $func . 'Pending']);
        }
        $this->clearQuery();
    }

    public static function getIdentifier(){
        return self::$uniqueIdentifier;
    }
    
    public static function setIdentifier($uid){
        self::$uniqueIdentifier = $uid;
    }
    
    protected function getRowKey($id)
    {
        foreach ($this->getData() as $key => $data)
        {
            if ($data->{self::getIdentifier()} == $id)
            {
                return $key;
                break;
            }
        }
        throw new \Exception('No data found with ID: ' . $id);
    }

    protected function setFields()
    {
        $this->tableFields = new \stdClass();
        foreach ($this->schema() as $field => $type)
            $this->tableFields->{$field} = (Validate::isNumeric($type) and $field != self::getIdentifier()) ? 0 : null;
        return $this;            
    }

    public function set($data)
    {
        foreach ($data as $name => $value){
            if ($this->checkField($name) && $this->checkType($name,$value))
                $this->tableFields->{$name} = utf8_encode($value);
        }
    }
    
    public function addEntry($data = [])
    {
        if(!$data)
            return false;
        if(!isset($data[self::getIdentifier()]))
            $this->clearQuery();
        foreach ($data as $name => $value){
            if ($this->checkField($name) && $this->checkType($name,$value))
                $this->tableFields->{$name} = utf8_encode($value);
        }
        $this->store();
    }
    
    public function getEntry($entryId = null)
    {
        $this->select($entryId);
        return $this->documentEntries;
    }
    
    public function checkField($name)
    {
        if (!in_array($name, $this->fields()))
            $this->addFields([$name=>'string']);
        return true;
    }

    public function checkType($name, $value)
    {
        $schema = $this->schema();
        if (array_key_exists($name, $schema) && $schema[$name] == gettype($value))
            return true;
        throw new \Exception('Wrong data type');
    }

    public static function removeDatabase($databaseName)
    {
        return (Document::load($databaseName)->remove() && Document::loadConfig($databaseName)->remove()) ? true : false;
    }

    public function addFields(array $fields)
    {
        $fields = Validate::arrToLower($fields);
        Validate::types(array_values($fields));
        $fields = array_diff_assoc($fields, $this->schema());
        if (!empty($fields))
        {
            $config         = $this->config();
            $config->schema = array_merge($this->schema(), $fields);
            $data = $this->getData();
            foreach ($data as $key => $object)
            {
                foreach($fields as $name => $type)
                    $data[$key]->{$name} = (Validate::isNumeric($type)) ? 0 : null;
            }
            $this->databaseData->put($data);
            $this->databaseConfig->put($config);
        }
    }

    public function deleteFields(array $fields)
    {
        $fields = Validate::arrToLower($fields);
        if (($key = array_search(self::getIdentifier(), $fields)) !== false)
            throw new \Exception('You can not delete the id field');
        $diff   = array_diff($fields,$this->fields());
        if (!empty($diff))
            throw new \Exception('Field(s) "' . implode(', ', $diff) . '" does not exists in table');
        $config         = $this->config();
        $config->schema = array_diff_key($this->schema(), array_flip($fields));
        $data = $this->getData();
        foreach ($data as $key => $object)
        {
            foreach ($fields as $name)
                unset($data[$key]->{$name});
        }
        $this->databaseData->put($data);
        $this->databaseConfig->put($config);
    }

    public function getName()
    {
        return $this->databasename;
    }

    public function config()
    {
        return $this->databaseConfig->get();
    }

    public function fields()
    {
        return array_keys($this->databaseConfig->getKey('schema', true));
    }

    public function schema()
    {
        return $this->databaseConfig->getKey('schema', true);
    }

    public function lastId()
    {
        return $this->databaseConfig->getKey('last_id');
    }

    public function store()
    {
        $data = $this->getData();
        if (!$this->currentId)
        {
            $config = $this->config();
            $config->last_id++;
            $this->tableFields->{self::getIdentifier()} = $config->last_id;
            array_push($data,$this->tableFields);
            $this->databaseConfig->put($config);
        }
        else
        {
            $this->tableFields->{self::getIdentifier()} = $this->currentId;
            $data[$this->currentKey]    = $this->tableFields;
        }
        $this->databaseData->put($data);
    }

    public function delete()
    {
        $data = $this->getData();
        if (isset($this->currentId))
            unset($data[$this->currentKey]);
        else
            $data = array_diff_key($data, $this->documentEntries);
        $this->documentEntries = array_values($data);
        return $this->databaseData->put($this->documentEntries) ? true : false;
    }

    public function select($id = null)
    {
        if ($id !== null)
        {
            $this->currentId        = $id;
            $this->currentKey       = $this->getRowKey($id);
            $this->documentEntries  = $this->getData()[$this->currentKey];
            foreach ($this->documentEntries as $field => $value)
                $this->tableFields->{$field} = $value;
        }
        else
            $this->filterEntries();
        return $this;
    }
    
    public function first(){
        $data = $this->getData();
        return (!isset($data[0])) ? false : $data[0];
    }
    
    public function last(){
        $data = $this->getData();
        return (!$data) ? false : end($data);
    }
    
    protected function clearQuery()
    {
        $this->documentfilters  = [];
        $this->currentId        = $this->currentKey = NULL;
    }
    
    public function getIterator()
    {
        return new \ArrayIterator($this->documentEntries);
    }
    
    
    
    
    
    
    

    public function count()
    {
        return count($this->documentEntries);
    }
    
    public function limit($number, $offset = 0)
    {
        $this->documentEntries = array_slice($this->documentEntries, $offset, $number);
        return $this;
    }        
}
