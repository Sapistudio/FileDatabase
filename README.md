Installation
------------
```
composer require sapistudio/filedatabase
```
##### Ending methods

- `addFields()` - append new fields into existing table
- `deleteFields()` - removing fields from existing table
- `save()` - insert or Update data.
- `delete()` - deleting data.
- `getConfig()` - returns object with configuration.
- `fields()` - returns array with fields name.
- `schema()` - returns assoc array with fields name and fields type `field => type`.
- `get()` - returns one row with specified ID.
- `findAll()` - returns all rows
- `asArray()` - returns data as indexed or assoc array: `['field_name' => 'field_name']`. Should be used after ending method `select`.
- `count()` - returns the number of rows. Should be used after ending method `find_all()` or `find()`.

### Initiate
```php
use \SapiStudio\FileDatabase\Handler as Database;
$dbObject = Database::load($dbname,['dir' => 'path/to/database/dir','fields' => $[{field_name} => {field_type}]]);
```
	
### Remove database
```php
$dbObject->removeDatabase();
```

### Check if a database exists
```php
$dbObject->exists();//return boolean
```

#### Multiple select
```php
$rows = $dbObject->findAll();
    
foreach($rows as $row)
{
    print_r($row);
}
```
#### Single record select
```php
$row = $dbObject->get(1);
echo $row->id;
```

### Insert
If the field doesnt exists,it will append that field,with type of string
```php
$dbObject->nickname = 'new_user';
$dbObject->save();
```
Do not set the ID.

### Update

```php
$row = $dbObject->get(1); //Edit row with ID 1
$row->nickname = 'edited_user';
$row->save();
```

#### Single record deleting
```php
$dbObject->get(1)->delete(); //Will remove row with ID 1
```
#### Multiple records deleting
```php
$dbObject->where('name', '=', 'John')->find()->delete(); //Will remove John from DB
```
#### Clear table
```php
$dbObject->findAll()->delete();

// OR

$dbObject->delete();
```
