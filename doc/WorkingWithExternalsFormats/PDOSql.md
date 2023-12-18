## Import Module
```php
use MammothPHP\WoollyM\IO\SQL;
```

## Create or import from a SQL Query

With a new DataFrame:
```php
$df = SQL::fromSql($pdo, 'SELECT foo, bar, baz FROM table_name;')->import();
```

Ton an existing DataFrame:
```php
SQL::fromSql($pdo, 'SELECT foo, bar, baz FROM table_name;')->import($df);
```

### Committing to a database:

```php
SQL::fromDataFrame($df)->toPDO($pdo, 'table_name');
```

With options:
```php
SQL::fromDataFrame($df)->options(chunkSize: 10)->toPDO($pdo, 'table_name');
```