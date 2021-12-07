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
Returns the given query with its defined parameters.
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
Returns the given query with SQL FROM.
```php
$query = $api->select();
$query = $api->params($query, '*');
$query = $api->from($query);
// $query = 'SELECT * FROM ';
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
Returns the given query with the specified columns to insert values at.
```php
$query = $api->columns($query, array($arg1, $arg2, ..., $argN));

Example:
$query = $api->insert();
$query = $api->table($query, 'table');
$query = $api->columns($query, array('column1', 'column2', 'column3'));
// $query = 'INSERT INTO table (column1, column2, column3) ';
```

## $api->values($string)
Returns the given query with SQL VALUES.
```php
$query = $api->insert();
$query = $api->table($query, 'table');
$query = $api->columns($query, array('column1', 'column2'));
$query = $api->values($query);
// $query = 'INSERT INTO table (column1, column2) VALUES ';
```

To do an insert query, do
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

</p>
</details>