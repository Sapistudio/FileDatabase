<?php
namespace SapiStudio\FileDatabase;

class Config extends Document{
    
    protected static $uniqueIdentifier = 'id';
    protected $configOptions;
    
    public function __construct($config = [])
    {
        foreach ($config as $key => $value)
            $this->configOptions->{$key} = $value;
        if($this->getOption('dir'))
            $this->setDir($this->getOption('dir'));
    }
    
    public function setName($name)
    {
        return parent::setName($name.'.config');
    }
    
    public function getOption($name){
        return (isset($this->configOptions->{$name})) ? $this->configOptions->{$name} : false;    
    }
    
    public function getIdentifier(){
        return self::$uniqueIdentifier;
    }
    
    public function setIdentifier($uid){
        self::$uniqueIdentifier = $uid;
    }
}