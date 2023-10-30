### Reading a CSV file:

```
x|y|z
1|2|3
4|5|6
7|8|9
```

```php
$df = DataFrame::fromCSV($fileName, [
    'sep' => '|',
    'colmap' => [
	    'x' => 'a',
        'y' => 'b',
        'z' => 'c'
    ]
]);
```

### Writing a CSV file:

```php
$df->toCSV($fileName);
```

```
"a","b","c"
"1","2","3"
"4","5","6"
"7","8","9"
```

### Reading a fixed-width file:

```
foo bar baz
-----------
1   2   3
4   5   6
7   8   9
```

```php
$df = DataFrame::fromFWF($fileName, [
	'a' => [0, 1],
    'b' => [4, 5],
    'c' => [8, 9]
], ['include' => '^[0-9]']);

```