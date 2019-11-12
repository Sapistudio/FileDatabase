<?php
namespace SapiStudio\FileDatabase;

class Query extends Query\Logic {

    protected $fields  = [];
    protected $limit   = 0;
    protected $offset  = 0;
    protected $sortBy  = 'ASC';
    protected $orderBy = '';
    protected $documents = [];

    /** Query::select()*/
    public function select($fields)
    {
        if (is_string($fields))
            $fields = explode(',',trim($fields));
        if (is_array($fields))
            $this->fields = $fields;
        return $this;
    }

    /** Query::where()*/
    public function where(...$arg)
    {
        $this->addAndFilter($arg);
        return $this;
    }

    /** Query::andWhere()*/
    public function andWhere(...$arg)
    {
        $this->addAndFilter($arg);
        return $this;
    }

    /** Query::orWhere()*/
    public function orWhere(...$arg)
    {
        $this->addOrFilter($arg);
        return $this;
    }

    /** Query::limit()*/
    public function limit($limit, $offset = 0)
    {
        $this->limit   = (int) $limit;
        if ($this->limit === 0)
            $this->limit = 9999999;
        $this->offset  = (int) $offset;
        return $this;
    }
    
    /** Query::orderBy()*/
    public function orderBy($field, $sort)
    {
        $this->orderBy = $field;
        $this->sortBy  = $sort;
        return $this;
    }

    /** Query::addAndFilter()*/
    protected function addAndFilter($arg)
    {
        $this->filters->add('and', $arg);
    }
    
    /** Query::addOrFilter()*/
    protected function addOrFilter($arg)
    {
        $this->filters->add('or', $arg);
    }

    /** Query::results()*/
    public function find()
    {
        return parent::runQuery();
    }
    
    /** Query::get()*/
    public function get()
    {
        return $this->find()->toArray();
    }
}
