### Querying from a database:

```php
$pdo = new PDO('sqlite::memory:');
$df = DataFrame::fromSQL('SELECT foo, bar, baz FROM table_name;', $pdo);
```

### Committing to a database:

```php
$pdo = new PDO('sqlite::memory:');
$affected = $df->toSQL('table_name', $pdo);
echo sprintf('%d rows committed to database.', $affected);
```