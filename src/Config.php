<?php
namespace SapiStudio\FileDatabase;

class Config extends Document{
    
    protected static $uniqueIdentifier = 'id';
    protected $configOptions;
    
    /** Config::__construct() */
    public function __construct($config = [])
    {
        if($config){
            foreach ($config as $key => $value)
                 @$this->configOptions->{$key} = $value;
        }
        //if($this->getOption('dir'))
            //$this->setDir($this->getOption('dir'));
    }
    
    /** Config::setName()*/
    public function setName($name)
    {
        return parent::setName($name.'.config');
    }
    
    /** Config::getOption()*/
    public function getOption($name){
        return (isset($this->configOptions->{$name})) ? $this->configOptions->{$name} : false;    
    }
    
    /** Config::getIdentifier()*/
    public function getIdentifier(){
        return self::$uniqueIdentifier;
    }
    
    /** Config::setIdentifier()*/
    public function setIdentifier($uid){
        self::$uniqueIdentifier = $uid;
    }
}
