### Reading an XLSX spreadsheet:

```php
$dfA = DataFrame::fromXLSX($fileName, ['sheetname' => 'Sheet A']);
$dfB = DataFrame::fromXLSX($fileName, ['sheetname' => 'Sheet B']);
$dfC = DataFrame::fromXLSX($fileName, ['sheetname' => 'Sheet C']);
```

### Writing an XLSX spreadsheet:

```php
$phpExcel = new PHPExcel();
$dfA->toXLSXWorksheet($phpExcel, 'Sheet A');
$dfB->toXLSXWorksheet($phpExcel, 'Sheet B');
$dfC->toXLSXWorksheet($phpExcel, 'Sheet C');
$writer = new PHPExcel_Writer_Excel2007($phpExcel);
$writer->save($fileName);
```
