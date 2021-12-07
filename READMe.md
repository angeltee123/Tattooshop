# NJC Tattoo
## API Documentation
<details><summary>MYSQLI FUNCTIONS</summary>
<p>

<details><summary>**SELECT FUNCTIONS**</summary>
<p>

## $api->select()
Returns 'SELECT ' to the string.
```php
$query = $api->select();
// $query = 'SELECT ';
```

## $api->params($string, $params)
Returns the given query and with its defined parameters.
To specify a single parameter, do
```php
$query = $api->params($query, '*');
// $query = 'SELECT * ';
```

To specify multiple parameters, do
```php
$query = $api->params($query, array($arg1, $arg2, ..., $argN));

Example:
$query = $api->params($query, array('column1', 'column2', 'column 3'))
// $query = 'SELECT column1, column2, column3 ';
```

</p>
</details>
