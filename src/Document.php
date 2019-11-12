<?php
namespace SapiStudio\FileDatabase;

class Document{

    protected $name;
    protected $fileExtension = '.json';
    protected $databaseDir   = null;

    /** Document::load()*/
    public static function load($name,$dir=null)
    {
        return (new self())->setName($name)->setDir($dir);
    }
    
    /** Document::getName()*/
    public function getName()
    {
        if(trim($this->name) == '')
            throw new \Exception('Invalid database');
        return $this->name;
    }
    
    /** Document::setName()*/
    public function setName($name)
    {
        if (empty($name))
            throw new \Exception('Invalid characters in database name:'.$name);
        $this->name = $name;
        return $this;
    }
    
    /** Document::setExtension()*/
    public function setExtension($ext){
        $this->fileExtension = '.'.$ext;
        return $this;
    }
    
    /** Document::getExtension() */
    public function getExtension(){
        return $this->fileExtension;
    }
    
    /** Document::setDir()*/
    public function setDir($dir = null){
        $dir = (is_null($dir)) ? realpath(__DIR__).DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR : $dir;
        if(!is_dir($dir))
            mkdir($dir,0755,true);
        $this->databaseDir = (is_null($dir)) ? realpath(__DIR__).DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR : $dir;
        return $this;
    }
    
    /** Document::getDir()*/
    public function getDir(){
        return $this->databaseDir;
    }
    
    /** Document::getPath()*/
    public final function getPath()
    {
        return $this->getDir().$this->getName().$this->getExtension();
    }
    
    /** Document::getKey()*/
    public function getKey($field, $assoc = false)
    {
        return $assoc ? $this->get($assoc)[$field] : $this->get($assoc)->{$field};
    }
    
    /** Document::get()*/
    public final function get($assoc = false)
    {
        return json_decode(file_get_contents($this->getPath()),$assoc);
    }

    /** Document::put()*/
    public final function put($data)
    {
        return file_put_contents($this->getPath(), json_encode($data));
    }

    /** Document::exists()*/
    public final function exists()
    {
        return file_exists($this->getPath());
    }

    /** Document::remove()*/
    public final function remove()
    {
        if ($this->exists())
        {
            if (unlink($this->getPath()))
                return true;
            throw new \Exception($this->getName() . ': Deleting failed');
        }
    }
}
