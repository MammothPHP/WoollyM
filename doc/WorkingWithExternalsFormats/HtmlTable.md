## Import Module
```php
use MammothPHP\WoollyM\IO\HTML;
```

## Get an HTML table:

```php
HTML::fromDataFrame($df)->toString(
    bool $pretty = true,
    ?int $limit = null,
    ?int $offset = 0,
    ?string $class = null,
    ?string $id = null
): string;
```