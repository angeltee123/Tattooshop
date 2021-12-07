# NJC Tattoo
## API Documentation
<details><summary>MYSQLI FUNCTIONS</summary>
<p>

<details><summary>SELECT FUNCTIONS</summary>
<p>

## $api->select()
Returns SQL SELECT to the calling string.
```php
$query = $api->select();
// $query = 'SELECT ';
```


## $api->params($string, $params)
Returns the given query string with its defined parameters.
To specify a single parameter, do
```php
$query = $api->params($query, '*');
// $query = 'SELECT * ';
```

To specify multiple parameters, do
```php
$query = $api->params($query, array($arg1, $arg2, ..., $argN));

Example:
$query = $api->select();
$query = $api->params($query, array('column1', 'column2', 'column3'));
// $query = 'SELECT column1, column2, column3 ';
```


## $api->from($string)
Returns the given query string with SQL FROM.
```php
$query = $api->select();
$query = $api->params($query, '*');
$query = $api->from($query);
// $query = 'SELECT * FROM ';
```

To construct a select query, do
```php
$query = $api->select();
$query = $api->params($query, '*');
$query = $api->from($query);
$query = $api->table($query, 'table');
// $query = 'SELECT * FROM table';
```

</p>
</details>

<details><summary>INSERT FUNCTIONS</summary>
<p>

## $api->insert()
Returns SQL INSERT to the calling string.
```php
$query = $api->insert();
// $query = 'INSERT INTO ';
```


## $api->columns($string, $params = array())
Returns the given query string with the specified columns to insert values at.
```php
$query = $api->columns($query, array($arg1, $arg2, ..., $argN));

Example:
$query = $api->insert();
$query = $api->table($query, 'table');
$query = $api->columns($query, array('column1', 'column2', 'column3'));
// $query = 'INSERT INTO table (column1, column2, column3) ';
```


## $api->values($string)
Returns the given query string with SQL VALUES.
```php
$query = $api->insert();
$query = $api->table($query, 'table');
$query = $api->columns($query, array('column1', 'column2'));
$query = $api->values($query);
// $query = 'INSERT INTO table (column1, column2) VALUES ';
```

To construct an insert query, do
```php
$query = $api->insert();
$query = $api->table($query, 'table');
$query = $api->columns($query, array('column1', 'column2', 'column3'));
$query = $api->values($query);
$query = $api->columns($query, array('value1', 'value2', 'value3'));
// $query = 'INSERT INTO table (column1, column2, column3) VALUES (value1, value2, value3)';
```

</p>
</details>

<details><summary>UPDATE FUNCTIONS</summary>
<p>

## $api->update()
Returns SQL UPDATE to the calling string.
```php
$query = $api->update();
// $query = 'UPDATE ';
```


## $api->set($string, $cols, $params)
Returns the given query string with the specified column-value pairs.
To specify a single column-value pair, do
```php
$query = $api->set($query, $column, $value);
```

To specify multiple column-value pairs, do
```php
$query = $api->set($query, array($col1, $col2, ..., $colN), array($value1, $value2, ..., $valueN));

Example:
$query = $api->update();
$query = $api->table($query, 'table');
$query = $api->set($query, array('column1', 'column2', 'column3'), array('value1', 'value2', 'value3'));
// $query = 'UPDATE table SET column1=value1, column2=value2, column3=value3 ';
```

To construct an update query, do
```php
$query = $api->update();
$query = $api->table($query, 'table');
$query = $api->set($query, array('column1', 'column2', 'column3'), array('value1', 'value2', 'value3'));
$query = $api->where($query, 'column', 'value');
// $query = 'UPDATE table SET column1=value1, column2=value2, column3=value3 WHERE column=value';
```

</p>
</details>

</p>
</details>