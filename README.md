Installation
------------
```
composer require greg0/lazer-database
```

##### Ending methods

- `addFields()` - append new fields into existing table
- `deleteFields()` - removing fields from existing table
- `set()` - get key/value pair array argument to save.
- `store()` - insert or Update data.
- `delete()` - deleting data.
- `config()` - returns object with configuration.
- `fields()` - returns array with fields name.
- `schema()` - returns assoc array with fields name and fields type `field => type`.
- `lastId()` - returns last ID from table.
- `select()` - returns one row with specified ID.
- `asArray()` - returns data as indexed or assoc array: `['field_name' => 'field_name']`. Should be used after ending method `select`.
- `count()` - returns the number of rows. Should be used after ending method `find_all()` or `find()`.

### Create database
```php
SapiStudio\FileDatabase\Handler::createDatabase('table_name',[{field_name} => {field_type}]);
```
	
### Remove database
```php
SapiStudio\FileDatabase\Handler::remove('table_name');
```

### Load database
```php
SapiStudio\FileDatabase\Handler::load('table_name');
```

### Check if a database exists
```php
  SapiStudio\FileDatabase\Handler::dbExists('table_name')->exists();//return boolean
```

#### Multiple select
```php
$table = SapiStudio\FileDatabase\Handler::load('table_name')->select();
    
foreach($table as $row)
{
    print_r($row);
}
```
#### Single record select
```php
$row = SapiStudio\FileDatabase\Handler::load('table_name')->select(1);

echo $row->id;
```

### Insert
If the field doesnt exists,it will append that field,with type of string
```php
$row = SapiStudio\FileDatabase\Handler::load('table_name');
$row->nickname = 'new_user';
$row->save();
```
Do not set the ID.

### Update

```php
$row = SapiStudio\FileDatabase\Handler::load('table_name')->find(1); //Edit row with ID 1
$row->nickname = 'edited_user';
$row->save();
```

#### Single record deleting
```php
SapiStudio\FileDatabase\Handler::load('table_name')->find(1)->delete(); //Will remove row with ID 1

// OR

SapiStudio\FileDatabase\Handler::load('table_name')->where('name', '=', 'John')->find()->delete(); //Will remove John from DB

```
#### Multiple records deleting
```php
SapiStudio\FileDatabase\Handler::load('table_name')->where('nickname', '=', 'edited_user')->delete();
```
#### Clear table
```php
SapiStudio\FileDatabase\Handler::load('table_name')->delete();
```
