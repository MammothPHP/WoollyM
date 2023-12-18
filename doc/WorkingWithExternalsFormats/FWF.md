## Import Module
```php
use MammothPHP\WoollyM\IO\FWF;
```

### Reading a fixed-width file:

### Import methods
```php
$fwfBuilder = FWF::fromFilePath($path);
$fwfBuilder = FWF::fromString($string);
$fwfBuilder = FWF::fromFileInfo(SplFileInfo $fileInfo); // or extending FileInfo like SplFileObject
```

### Reading
```
foo bar baz
-----------
1   2   3
4   5   6
7   8   9
```

```php
$df = FWF::fromFilePath($filePath)
    ->format(colSpecs: [
        'a' => [0, 1],
        'b' => [4, 5],
        'c' => [8, 9]
    ])
    ->filter($includeRegexOpt: '^[0-9]', $excludeRegexOpt: '%')
    ->import();
```

### Import to an existing DataFrame
```php
FWF:fromFilePath($path)->->format(colSpecs: [...])->import(to: $df);
```