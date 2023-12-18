## Import Module
```php
use MammothPHP\WoollyM\IO\JSON;
```

## Reading a JSON file:

### Import methods
```php
$JSONBuilder = JSON::fromFilePath($path);
$JSONBuilder = JSON::fromString($string);
$JSONBuilder = JSON::fromFileInfo(SplFileInfo $fileInfo); // or extending FileInfo like SplFileObject
```

### Simple import
```php
$df = JSON::fromFilePath($path)->import();
```

### Import to an existing DataFrame
```php
JSON::fromFilePath($path)->import($df);
```

## Export to JSON
```php
JSON::fromDataFrame($df)->toFile(string|SplFileInfo $file, bool $overwriteFile = false, bool $pretty = false): void; // if string => a stream path
JSON::fromDataFrame($df)->toString(bool $pretty): string;
```
