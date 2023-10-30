### Converting to JSON:

```php
$json = $df->toJSON();
```

### Creating from JSON:

```php
$df = DataFrame::fromJSON('[
    {"a": 1, "b": 2, "c": 3},
    {"a": 4, "b": 5, "c": 6},
    {"a": 7, "b": 8, "c": 9}
]');
```