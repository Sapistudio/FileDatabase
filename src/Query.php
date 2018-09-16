<?php
namespace SapiStudio\FileDatabase;

class Query extends Query\Logic {

    protected $fields  = [];
    protected $limit   = 0;
    protected $offset  = 0;
    protected $sortBy  = 'ASC';
    protected $orderBy = '';
    protected $documents = [];

    /**
     * Query::select()
     * 
     * @return
     */
    public function select($fields)
    {
        if (is_string($fields))
            $fields = explode(',',trim($fields));
        if (is_array($fields))
            $this->fields = $fields;
        return $this;
    }

    /**
     * Query::where()
     * 
     * @return
     */
    public function where(...$arg)
    {
        $this->addAndFilter($arg);
        return $this;
    }

    /**
     * Query::andWhere()
     * 
     * @return
     */
    public function andWhere(...$arg)
    {
        $this->addAndFilter($arg);
        return $this;
    }

    /**
     * Query::orWhere()
     * 
     * @return
     */
    public function orWhere(...$arg)
    {
        $this->addOrFilter($arg);
        return $this;
    }

    /**
     * Query::limit()
     * 
     * @return
     */
    public function limit($limit, $offset = 0)
    {
        $this->limit   = (int) $limit;
        if ($this->limit === 0)
            $this->limit = 9999999;
        $this->offset  = (int) $offset;
        return $this;
    }
    
    /**
     * Query::orderBy()
     * 
     * @return
     */
    public function orderBy($field, $sort)
    {
        $this->orderBy = $field;
        $this->sortBy  = $sort;
        return $this;
    }

    /**
     * Query::addAndFilter()
     * 
     * @return
     */
    protected function addAndFilter($arg)
    {
        $this->filters->add('and', $arg);
    }
    
    /**
     * Query::addOrFilter()
     * 
     * @return
     */
    protected function addOrFilter($arg)
    {
        $this->filters->add('or', $arg);
    }

    /**
     * Query::results()
     * 
     * @return
     */
    public function find()
    {
        return parent::runQuery();
    }
    
    /**
     * Query::get()
     * 
     * @return
     */
    public function get()
    {
        return $this->find()->toArray();
    }
}
