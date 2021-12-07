# NJC Tattoo
## API Documentation
<details><summary>MYSQLI FUNCTIONS</summary>
<p>

<details><summary>SELECT FUNCTIONS</summary>
<p>

## $api->select()
Returns 'SELECT ' to the calling string.
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
$query = $api->params($query, array('column1', 'column2', 'column3'));
// $query = 'SELECT column1, column2, column3 ';
```

## $api->from($string)
Returns the given query with SQL FROM
```php
$query = $api->select();
$query = $api->params($query, '*');
$query = $api->from($query);
// $query = 'SELECT * FROM ';
```

</p>
</details>
