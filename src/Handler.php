<?php
namespace SapiStudio\FileDatabase;

class Handler implements \Countable{

    protected $databaseConfig   = null;
    protected $databaseDocument = null;
    protected $documentEntries  = [];
    protected $databasename     = null;
    protected $tableFields      = null;
    protected $currentId;
    protected $currentKey;
    
    /**
     * Handler::load()
     * 
     * @return
     */
    public static function load($databaseName,array $options = [])
    {
        return new static($databaseName,$options);
    }
    
    /**
     * Handler::__construct()
     * 
     * @return
     */
    public function __construct($databaseName = null,array $configOptions = []){
        $this->databasename     = $databaseName;
        $this->databaseConfig   = (new Config($configOptions))->setName($databaseName);
        $this->databaseDocument = (new Document)->setName($databaseName)->setDir($this->databaseConfig->getDir());
        if (!$this->dbExists()){
            $fields = Validate::arrToLower($this->databaseConfig->getOption('fields'));
            Validate::types(array_values($fields));
            if(!array_key_exists($this->databaseConfig->getIdentifier(),$fields))
                $fields = [$this->databaseConfig->getIdentifier() => 'integer'] + $fields;
                $configData            = new \stdClass();
                $configData->schema    = $fields;
                $this->databaseDocument->put([]);
                $this->databaseConfig->put($configData);
        }
        return $this->setFields();
    }
    
    /**
     * Handler::dbExists()
     * 
     * @return
     */
    public function dbExists(){
        return ($this->databaseDocument->exists() && $this->databaseConfig->exists()) ? true : false;
    }
    
    /**
     * Handler::removeDatabase()
     * 
     * @return
     */
    public function removeDatabase()
    {
        return ($this->databaseDocument->remove() && $this->databaseConfig->remove()) ? true : false;
    }
    
    /**
     * Handler::getName()
     * 
     * @return
     */
    public function getName()
    {
        return $this->databasename;
    }

    /**
     * Handler::fields()
     * 
     * @return
     */
    public function fields()
    {
        return array_keys($this->databaseConfig->getKey('schema', true));
    }

    /**
     * Handler::schema()
     * 
     * @return
     */
    public function schema()
    {
        return $this->databaseConfig->getKey('schema', true);
    }
    
    /**
     * Handler::__set()
     * 
     * @return
     */
    public function __set($name, $value)
    {
        if ($this->checkField($name))
            $this->tableFields->{$name} = \utf8_encode($value);
    }

    /**
     * Handler::__get()
     * 
     * @return
     */
    public function __get($name)
    {
        return (isset($this->tableFields->{$name})) ? $this->tableFields->{$name} : false;
    }

    /**
     * Handler::__isset()
     * 
     * @return
     */
    public function __isset($name)
    {
        return isset($this->tableFields->{$name});
    }
    
    /**
     * Handler::getData()
     * 
     * @return
     */
    protected function getData()
    {
        return $this->databaseDocument->get();
    }
    
    /**
     * Handler::getConfig()
     * 
     * @return
     */
    public function getConfig()
    {
        return $this->databaseConfig->get();
    }
    
    /**
     * Handler::setDocuments()
     * 
     * @param mixed $documentsData
     * @return
     */
    public function setDocuments($documentsData = []){
        $this->documentEntries  = $documentsData;
        return $this;
    }
    
    /**
     * Handler::getDocuments()
     * 
     * @return
     */
    public function getDocuments(){
        return $this->documentEntries;
    }

    /**
     * Handler::query()
     * 
     * @return
     */
    public function query()
    {
        return new Query($this);
    }
    
    /**
     * Handler::getRowKey()
     * 
     * @return
     */
    protected function getRowKey($id)
    {
        foreach ($this->getData() as $key => $data)
        {
            if ($data->{$this->databaseConfig->getIdentifier()} == $id)
            {
                return $key;
                break;
            }
        }
        throw new \Exception('No data found with ID: ' . $id);
    }

    /**
     * Handler::setFields()
     * 
     * @return
     */
    protected function setFields()
    {
        $this->tableFields = new \stdClass();
        foreach ($this->schema() as $field => $type)
            $this->tableFields->{$field} = (Validate::isNumeric($type) and $field != $this->databaseConfig->getIdentifier()) ? 0 : null;
        $this->clearIdentifier();
        return $this;            
    }
    
    /**
     * Handler::checkField()
     * 
     * @return
     */
    public function checkField($name)
    {
        return (!in_array($name, $this->fields())) ?  $this->addFields([$name=>'string']) : true;
    }

    /**
     * Handler::addFields()
     * 
     * @return
     */
    public function addFields(array $fields)
    {
        $fields = Validate::arrToLower($fields);
        Validate::types(array_values($fields));
        $fields = array_diff_assoc($fields, $this->schema());
        if (!empty($fields))
        {
            $config         = $this->getConfig();
            $config->schema = array_merge($this->schema(), $fields);
            $data = $this->getData();
            foreach ($data as $key => $object)
            {
                foreach($fields as $name => $type)
                    $data[$key]->{$name} = (Validate::isNumeric($type)) ? 0 : null;
            }
            $this->databaseDocument->put($data);
            $this->databaseConfig->put($config);
        }
        return true;
    }

    /**
     * Handler::deleteFields()
     * 
     * @return
     */
    public function deleteFields(array $fields)
    {
        $fields = Validate::arrToLower($fields);
        if (($key = array_search($this->databaseConfig->getIdentifier(), $fields)) !== false)
            throw new \Exception('You can not delete the id field');
        $diff   = array_diff($fields,$this->fields());
        if (!empty($diff))
            throw new \Exception('Field(s) "' . implode(', ', $diff) . '" does not exists in table');
        $config         = $this->getConfig();
        $config->schema = array_diff_key($this->schema(), array_flip($fields));
        $data = $this->getData();
        foreach ($data as $key => $object)
        {
            foreach ($fields as $name)
                unset($data[$key]->{$name});
        }
        $this->databaseDocument->put($data);
        $this->databaseConfig->put($config);
        return true;
    }

    /**
     * Handler::addEntry()
     * 
     * @return
     */
    public function addEntry($data = [])
    {
        if(!$data)
            return false;
        (!isset($data[$this->databaseConfig->getIdentifier()])) ? $this->clearIdentifier() : $this->get($data[$this->databaseConfig->getIdentifier()]);
        foreach ($data as $name => $value){
            if ($this->checkField($name))
                $this->tableFields->{$name} = utf8_encode($value);
        }
        return $this->save();
    }
    
    /**
     * Handler::store()
     * 
     * @return
     */
    public function save()
    {
        $data = $this->getData();
        if (!$this->currentId)
        {
            $config = $this->getConfig();
            $config->last_id++;
            $this->tableFields->{$this->databaseConfig->getIdentifier()} = $config->last_id;
            array_push($data,$this->tableFields);
            $this->databaseConfig->put($config);
        }
        else
        {
            $this->tableFields->{$this->databaseConfig->getIdentifier()} = $this->currentId;
            $data[$this->currentKey]    = $this->tableFields;
        }
        $this->databaseDocument->put($data);
        return (!$this->currentId) ? $this->setFields() : $this;
    }

    /**
     * Handler::delete()
     * 
     * @return
     */
    public function delete()
    {
        $data = $this->getData();
        if (isset($this->currentId))
            unset($data[$this->currentKey]);
        elseif($this->documentEntries)
            $data = array_diff_key($data,$this->documentEntries);
        else
            $data = [];
        $this->documentEntries = array_values($data);
        return $this->databaseDocument->put($this->documentEntries) ? true : false;
    }
    
    /**
     * Handler::truncate()
     * 
     * @return
     */
    public function truncate()
    {
        $config = $this->getConfig();
        $config->last_id = 0;
        $this->databaseConfig->put($config);
        $this->documentEntries = [];
        $this->databaseDocument->put([]);
        return $this->setFields();
    }

    /**
     * Handler::clearIdentifier()
     * 
     * @return void
     */
    protected function clearIdentifier(){
        $this->currentId = $this->currentKey = null;
    }
    
    /**
     * Handler::select()
     * 
     * @return
     */
    public function get($id = null)
    {
        if ($id == null)
            return false;
        $this->currentId        = $id;
        $this->currentKey       = $this->getRowKey($id);
        $this->setDocuments($this->getData()[$this->currentKey]);
        foreach ($this->documentEntries as $field => $value)
            $this->tableFields->{$field} = $value;
        return $this;
    }
    
    /**
     * Handler::select()
     * 
     * @return
     */
    public function findAll()
    {
        return $this->setDocuments($this->getData());
    }
    
    /**
     * Handler::first()
     * 
     * @return
     */
    public function first(){
        return (!isset($this->documentEntries[0])) ? false : $this->documentEntries[0];
    }
    
    /**
     * Handler::last()
     * 
     * @return
     */
    public function last(){
        return (!$this->documentEntries) ? false : end($this->documentEntries);
    }
    
    /**
     * Handler::toArray()
     * 
     * @return
     */
    public function toArray(){
        return json_decode(json_encode($this->getDocuments()),true);
    }

    /**
     * Handler::count()
     * 
     * @return
     */
    public function count()
    {
        return count($this->documentEntries);
    }
}
