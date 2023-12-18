## Import Module
```php
use MammothPHP\WoollyM\IO\{CSV, TSV};
```

## Reading a CSV file:

### Import methods
```php
$csvBuilder = CSV::fromFilePath($path);
$csvBuilder = CSV::fromString($string);
$csvBuilder = CSV::fromFileInfo(SplFileInfo $fileInfo); // or extending FileInfo like SplFileObject
$csvBuilder = CSV::fromCsvReader(League\Csv\Reader $csvReader);
$csvBuilder = CSV::fromStream($phpStreamFile);
```

### Simple import
```
x,y,z
1,2,3
4,5,6
7,8,9
```

```php
$df = CSV:fromFilePath($path)->import();
```

### Import to an existing DataFrame
```php
CSV:fromFilePath($path)->import(to: $df);
```

### Shortcut for TSV files
```php
$df = TSV:fromFilePath($path)->import();
```

### Without column
```
1,2,3
4,5,6
7,8,9
```

```php
$df = CSV:fromFilePath($path)->format(headerOffset: null, columns: ['a','b','c'])->import();
```

### Custom delimiters and mapping:
```
x|y|z
1|2|3
4|5|6
7|8|9
```

```php
$df = CSV:fromFilePath($path)
    ->format(
        delimiter: '|',
        mapping: [
            'x' => 'a',
            'y' => 'b',
            'z' => 'c'
        ]
    )
    ->import();
```

It's interpreted as:
```
a,b,c
1,2,3
4,5,6
7,8,9
```

### Custom enclosure & escape
```
x,y
foo,bar
\"",",bar
```

```php
$df = CSV:fromFilePath($path)
    ->format(
        enclosure '"', // is already the default value
        escape: '\\' // is already the default value
    )
    ->import();

$df->toArray();
[
    [
        'x' => 'foo',
        'y' => 'bar'
    ],
    [
        'x' => '",',
        'y' => 'bar'
    ]
]
```

### Filter
```
x|y|z
1|2|3
4|5|6
7|8|9
```

```php
$df = CSV:fromFilePath($path)->format(delimiter: '|')->filter(onlyColumns: ['x','z'])->import();
```

Interpret as:
```
x|z
1|3
4|6
7|9
```


## Export to CSV

### Export methods

```php
CSV::fromDataFrame($df)->toFile(string|SplFileInfo|Writer $file, bool $overwriteFile = false, bool $writeHeader = true): void; // if string, a stream path
CSV::fromDataFrame($df)->toString(bool $writeHeader = true): string;
CSV::fromDataFrame($df)->toStream(stream $phpStreamFile, bool $writeHeader = true): void;
```

### Formating options
Same formating options as import can be applied.

Example:
```php
CSV::fromDataFrame($df)->format(delimiter: '|')->toString();
```

### TSV shortcut
```php
TSV::fromDataFrame($df)->toString();
```
