## Import Module
```php
use MammothPHP\WoollyM\IO\{XLSX, ODS};
```

## Reading a XLSX file:

### Import methods
```php
$XLSXBuilder = XLSX::fromFilePath($path);
$XLSXBuilder = XLSX::fromString($string);
$XLSXBuilder = XLSX::fromFileInfo(SplFileInfo $fileInfo); // or extending FileInfo like SplFileObject
```

### Simple import
```php
$df = XLSX:fromFilePath($path)->import();
```

### Import to an existing DataFrame
```php
XLSX::fromFilePath($path)->import(to: $df);
```

### Import from ODF (.ods Open Document Format)
```php
// Just use the ODF class instead, all method and options are the same.
ODS::fromFilePath($path);
```


### Specify format
```php
$df = XLSX::fromFilePath($path)->format($sheetName = 'results2042', $colRow = 1)->import();
```

```$colRow:``` Parse data after specified line (starting at 1), and consider this line at the header. Set to 0 for no header.


## Export to XLSX

### Export methods

```php
XLSX::fromDataFrame($df)->toFile(string|SplFileInfo $filePath, bool $overwriteFile = false, string $worksheetTitle = 'DataFrame'): void;
XLSX::fromDataFrame($df)->toExcelSpreadsheet(PhpOffice\PhpSpreadsheet\Spreadsheet &$spreadsheet, string $worksheetTitle = 'Spread1'): PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
```

### Export to ODF (.ods Open Document Format)
```php
// Just use the ODF class instead, all method and options are the same.
ODS::fromDataFrame();
```