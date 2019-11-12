<?php
namespace SapiStudio\FileDatabase\Query;

use SapiStudio\FileDatabase\Handler as Database;

class Logic{
    
    protected $databaseHandler;
    protected $filters;

    /** Logic::__construct()*/
    public function __construct(Database $database)
    {
        $this->databaseHandler  = $database;
        $this->filters          = new Filter();
    }

    /**  Logic::runQuery() */
    public function runQuery()
    {
        $filters = $this->filters->get();
        $this->documents  = [];
        if (empty($filters))
            $filters = 'findAll';
        $this->documents = $this->databaseHandler->findAll()->getDocuments();
        if ($filters !== 'findAll')
            $this->filter($filters);
        $this->sort();
        $this->offsetLimit();
        if (is_array($this->fields) && !empty($this->fields))
        {
            foreach($this->documents as $index => $document)
            {
                $fields = new \stdClass();
                foreach($this->fields as $fieldTarget)
                    $fields->{$fieldTarget} = $document->{$fieldTarget};
                $this->documents[$index] = $fields;
            }
        }
        $this->databaseHandler->setDocuments($this->documents);
        return $this->databaseHandler;
    }

    /** Logic::filter()*/
    protected function filter($filters)
    {
        $results    = [];
        $documents  = $org_docs = $this->documents;
        if (isset($filters['and']) && !empty($filters['and']))
        {
            foreach($filters['and'] as $filter)
            {
                list($field, $operator, $value) = $filter;
                $documents = array_values(array_filter($documents, function ($document) use ($field, $operator, $value) {
                    return $this->match($document, $field, $operator, $value);
                }));
                $results = $documents;
            }
        }

        if (isset($filters['or']) && !empty($filters['or']))
        {
            foreach($filters['or'] as $filter)
            {
                list($field, $operator, $value) = $filter;
                $documents = array_values(array_filter($org_docs, function ($document) use ($field, $operator, $value) {
                    return $this->match($document, $field, $operator, $value);
                }));
                $results = array_unique(array_merge($results, $documents), SORT_REGULAR);
            }
        }
        $this->documents = $results;
    }

    /** Logic::offsetLimit()*/
    protected function offsetLimit()
    {
        if ($this->limit != 0 || $this->offset != 0)
            $this->documents = array_slice($this->documents, $this->offset, $this->limit);
    }

    /** Logic::sort()*/
    protected function sort()
    {
        $orderBy = $this->orderBy;
        $sortBy  = $this->sortBy;

        if ($orderBy=='')
            return false;
        usort($this->documents, function($a, $b) use ($orderBy, $sortBy) {
            $propA = $a->field($orderBy);
            $propB = $b->field($orderBy);
            if (strnatcasecmp($propB, $propA) == strnatcasecmp($propA, $propB)) {
                return 0;
            }
            if ($sortBy == 'DESC')
                return (strnatcasecmp($propB, $propA) < strnatcasecmp($propA, $propB)) ? -1 : 1;
            else
                return (strnatcasecmp($propA, $propB) < strnatcasecmp($propB, $propA)) ? -1 : 1;
        });
    }

    /** Logic::match() */
    public function match($document, $field, $operator, $value)
    {
        $d_value = $document->{$field};
        switch (true)
        {
            case ($operator === '=' && $d_value == $value):
                return true;
            case ($operator === '==' && $d_value == $value):
                return true;
            case ($operator === '===' && $d_value === $value):
                return true;
            case ($operator === '!=' && $d_value != $value):
                return true;
            case ($operator === '!==' && $d_value !== $value):
                return true;
            case (strtoupper($operator) === 'NOT' && $d_value != $value):
                return true;
            case ($operator === '>'  && $d_value >  $value):
                return true;
            case ($operator === '>=' && $d_value >= $value):
                return true;
            case ($operator === '<'  && $d_value <  $value):
                return true;
            case ($operator === '<=' && $d_value <= $value):
                return true;
            case (strtoupper($operator) === 'LIKE' && preg_match('/'.$value.'/is',$d_value)):
                return true;
            case (strtoupper($operator) === 'NOT LIKE' && !preg_match('/'.$value.'/is',$d_value)):
                return true;
            case (strtoupper($operator) === 'IN' && in_array($d_value, (array) $value)):
                return true;
            case (strtoupper($operator) === 'IN' && in_array($value, (array) $d_value)):
                return true;
            case (strtoupper($operator) === 'REGEX' && preg_match($value, $d_value)):
                return true;
            default:
                return false;
        }
    }
}
