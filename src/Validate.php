<?php
namespace SapiStudio\FileDatabase;

class Validate {

    /** Validate::isNumeric() */
    public static function isNumeric($type)
    {
        return (in_array($type, ['integer', 'double'])) ? true : false;
    }

    /** Validate::types()*/
    public static function types(array $types)
    {
        $diff    = array_diff($types, ['boolean', 'integer', 'string', 'double']);
        if (empty($diff))
            return true;
        throw new \Exception('Wrong types:. Available "boolean, integer, string, double"');
    }

    /** Validate::arrToLower()*/
    public static function arrToLower(array $array)
    {
        return array_map('strtolower', array_change_key_case($array));
    }
}
