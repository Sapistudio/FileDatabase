<?php
namespace SapiStudio\FileDatabase;

class Document{

    protected $name;
    protected $fileExtension = '.json';
    protected $databaseDir   = null;

    /**
     * Document::load()
     * 
     * @param mixed $name
     * @param mixed $dir
     * @return
     */
    public static function load($name,$dir=null)
    {
        return (new self())->setName($name)->setDir($dir);
    }
    
    /**
     * Document::getName()
     * 
     * @return
     */
    public function getName()
    {
        if(trim($this->name) == '')
            throw new \Exception('Invalid database');
        return $this->name;
    }
    
    /**
     * Document::setName()
     * 
     * @param mixed $name
     * @return
     */
    public function setName($name)
    {
        if (empty($name))
            throw new \Exception('Invalid characters in database name:'.$name);
        $this->name = $name;
        return $this;
    }
    
    /**
     * Document::setExtension()
     * 
     * @param mixed $ext
     * @return
     */
    public function setExtension($ext){
        $this->fileExtension = '.'.$ext;
        return $this;
    }
    
    /**
     * Document::getExtension()
     * 
     * @return
     */
    public function getExtension(){
        return $this->fileExtension;
    }
    
    /**
     * Document::setDir()
     * 
     * @param mixed $dir
     * @return
     */
    public function setDir($dir = null){
        $this->databaseDir = (is_null($dir)) ? realpath(__DIR__).DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR : $dir;
        return $this;
    }
    
    /**
     * Document::getDir()
     * 
     * @return
     */
    public function getDir(){
        return $this->databaseDir;
    }
    
    /**
     * Document::getPath()
     * 
     * @return
     */
    public final function getPath()
    {
        return $this->getDir().$this->getName().$this->getExtension();
    }
    
    /**
     * Document::getKey()
     * 
     * @param mixed $field
     * @param bool $assoc
     * @return
     */
    public function getKey($field, $assoc = false)
    {
        return $assoc ? $this->get($assoc)[$field] : $this->get($assoc)->{$field};
    }
    
    /**
     * Document::get()
     * 
     * @param bool $assoc
     * @return
     */
    public final function get($assoc = false)
    {
        return json_decode(file_get_contents($this->getPath()),$assoc);
    }

    /**
     * Document::put()
     * 
     * @param mixed $data
     * @return
     */
    public final function put($data)
    {
        return file_put_contents($this->getPath(), json_encode($data));
    }

    /**
     * Document::exists()
     * 
     * @return
     */
    public final function exists()
    {
        return file_exists($this->getPath());
    }

    /**
     * Document::remove()
     * 
     * @return
     */
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