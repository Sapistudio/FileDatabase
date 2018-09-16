<?php
namespace SapiStudio\FileDatabase;

class Config extends Document{
    
    protected static $uniqueIdentifier = 'id';
    protected $configOptions;
    
    /**
     * Config::__construct()
     * 
     * @return
     */
    public function __construct($config = [])
    {
        foreach ($config as $key => $value)
            $this->configOptions->{$key} = $value;
        if($this->getOption('dir'))
            $this->setDir($this->getOption('dir'));
    }
    
    /**
     * Config::setName()
     * 
     * @return
     */
    public function setName($name)
    {
        return parent::setName($name.'.config');
    }
    
    /**
     * Config::getOption()
     * 
     * @return
     */
    public function getOption($name){
        return (isset($this->configOptions->{$name})) ? $this->configOptions->{$name} : false;    
    }
    
    /**
     * Config::getIdentifier()
     * 
     * @return
     */
    public function getIdentifier(){
        return self::$uniqueIdentifier;
    }
    
    /**
     * Config::setIdentifier()
     * 
     * @return
     */
    public function setIdentifier($uid){
        self::$uniqueIdentifier = $uid;
    }
}
