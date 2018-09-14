Lazer - database based on JSON files
====================================
[![Build Status](https://travis-ci.org/Greg0/Lazer-Database.svg?branch=master)](https://travis-ci.org/Greg0/Lazer-Database)
[![Current Version](https://img.shields.io/packagist/v/greg0/lazer-database.svg)](https://packagist.org/packages/greg0/lazer-database#latest)
![PHP Version](https://img.shields.io/packagist/php-v/greg0/lazer-database.svg)

PHP Library to use JSON files like a database.   
Functionality inspired by ORM's

Requirements
------------
- PHP 5.6+
- Composer

Installation
------------
Easiest way to install `Lazer Database` is to use Composer. Of course you can use your own autoloader but you must configure it properly by yourself. You can find my Package on [Packagist.org](https://packagist.org/packages/greg0/lazer-database).

To add library to your dependencies, execute:
```
composer require greg0/lazer-database
```

Tests
-----
Easiest way to run unit tests is to use composer script 
```
composer run-script test
```

Structure of table files
-------

`table_name.data.json` - table file with data   
`table_name.config.json` - table file with configuration 

    
Basic Usage
-----------

First of all you should define constant `LAZER_DATA_PATH` containing absolute path to folder with JSON files:
```php
define('LAZER_DATA_PATH', realpath(__DIR__).'/data/'); //Path to folder with tables
```

Then set up namespace:
```php
use Lazer\Classes\Database as Lazer; // example
```

### Methods

##### Chain methods

- `limit()` - returns results between a certain number range. Should be used right before ending method `find_all()`.
- `orderBy()` - sort rows by key in order, can order by more than one field (just chain it). 
- `groupBy()` - group rows by field.
- `where()` - filter records. Alias: `and_where()`.
- `orWhere()` - other type of filtering results. 
- `with()` - join other tables by defined relations

##### Ending methods

- `addFields()` - append new fields into existing table
- `deleteFields()` - removing fields from existing table
- `set()` - get key/value pair array argument to save.
- `save()` - insert or Update data.
- `delete()` - deleting data.
- `relations()` - returns array with table relations
- `config()` - returns object with configuration.
- `fields()` - returns array with fields name.
- `schema()` - returns assoc array with fields name and fields type `field => type`.
- `lastId()` - returns last ID from table.
- `find()` - returns one row with specified ID.
- `findAll()` - returns rows.
- `asArray()` - returns data as indexed or assoc array: `['field_name' => 'field_name']`. Should be used after ending method `find_all()` or `find()`.
- `count()` - returns the number of rows. Should be used after ending method `find_all()` or `find()`.

### Create database
```php
Lazer::create('table_name', array(
    'id' => 'integer',
    'nickname' => 'string',
    {field_name} => {field_type}
));
```
More informations about field types and usage in PHPDoc
	
### Remove database
```php
Lazer::remove('table_name');
```

### Check if a database exists
```php
try{
    \Lazer\Classes\Helpers\Validate::table('table_name')->exists();
} catch(\Lazer\Classes\LazerException $e){
    //Database doesn't exist
}
```

### Select

#### Multiple select
```php
$table = Lazer::table('table_name')->findAll();
    
foreach($table as $row)
{
    print_r($row);
}
```
#### Single record select
```php
$row = Lazer::table('table_name')->find(1);

echo $row->id;
```
Type ID of row in `find()` method.

You also can do something like that to get first matching record:
```php
$row = Lazer::table('table_name')->where('name', '=', 'John')->find();

echo $row->id;
```

### Insert
```php
$row = Lazer::table('table_name');

$row->nickname = 'new_user';
$row->save();
```
Do not set the ID.

### Update

It's very smilar to `Inserting`.
```php
$row = Lazer::table('table_name')->find(1); //Edit row with ID 1

$row->nickname = 'edited_user';
$row->save();
```
### Remove

#### Single record deleting
```php
Lazer::table('table_name')->find(1)->delete(); //Will remove row with ID 1

// OR

Lazer::table('table_name')->where('name', '=', 'John')->find()->delete(); //Will remove John from DB

```
#### Multiple records deleting
```php
Lazer::table('table_name')->where('nickname', '=', 'edited_user')->delete();
```
#### Clear table
```php
Lazer::table('table_name')->delete();
```

