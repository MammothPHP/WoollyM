<p align="center">
    <picture>
        <img alt="" width="40%" src="logos/woollym_logo.png">
    </picture>
</p>

> Main Author: [Julien Boudry](https://www.linkedin.com/in/julienboudry/)  
> License: [BSD-3](LICENSE.txt) - _Please [say hello](https://github.com/MammothPHP/WoollyM/discussions/categories/your-wolly-projects) if you like or use this code!_  
> Contribute: [Contribute File](CONTRIBUTING.md)   
> Donation: **₿ [bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh](https://blockchair.com/bitcoin/address/bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh)** or **[Github Sponsor Page](https://github.com/sponsors/julien-boudry)**  
> _You can also offer me a bottle of good wine._  

> [!WARNING]
> This project is currently at an **experimental stage**. Production use is not recommended. APIs and functionalities are subject to **change at any time without notice**. Documentation is still deficient. Help and feedback are most welcome.

> [!NOTE]
> _Wolly was a fork from [archon/dataframe](https://github.com/hwperkins/Archon) project which was very useful and inspiring during development. Today, the internal engine has been almost completely rewritten and the public APIs are radically different and incompatible. A few traces of code and ideas remain, they have been placed by their original author under the BSD-3 license._

# WoollyM: PHP Dataframes for Data Analysis library

WoollyM is a PHP library for data analysis. It can be used to represent tabular data from various sources _(CSV, database, json, Excel...)_. The unified API can then be used easily to browse, analyze, modify, and export data in a variety of formats, we try to provide a very playful, modern, expressive, and user-friendly interface. This API is also modular and extensible, so you can easily add your own calculation and exploration methods.

Performances are optimized to be as light as possible on RAM during operations (input, output, read, write, stats, copy, clone), this is done using - internally - complex iterators and optimization preferring RAM over speed (even if we try to be fast also). The storage engine uses a modular storage system, if the default PhpArray driver uses RAM, the use of a database driver (such as the PDO driver) theoretically allows you to work on extremely large datasets. Using appropriate drivers, you can also write - for example - directly to the database (add, update) using the Wolly API.

## Installation

### Using Composer:

```sh
composer require mammothphp/woollym
```

### Requirements
 - PHP 8.3 or higher
 - php_mbstring extension


## Instanciation (basic)

### Instantiating from an array:

```php
$arr = [
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
];

$df = DataFrame::fromArray($arr);

// equivalent
$df = new DataFrame($arr);
```

### Import or export from/to an external source
_To limit external the base depencies, some module could require a separate `composer require`. Please consult the instructions for each of needed module._ 

| Module | Import | Export | Performances & Limit
| --- | --- | --- | ---
| [CSV/TSV](doc/WorkingWithExternalsFormats/CSV.md) | :heavy_check_mark: | :heavy_check_mark: | _Memory and performance optimized. It's a wrapper on top of [league/csv](https://csv.thephpleague.com/)_
| [FWF](doc/WorkingWithExternalsFormats/FWF.md) | :heavy_check_mark: | :x: | _Limited_
| [JSON](doc/WorkingWithExternalsFormats/JSON.md) | :heavy_check_mark: | :heavy_check_mark: | _Memory and performance optimized on import only, it's a wrapper on top of [halaxa/json-machine](https://github.com/halaxa/json-machine). Can be limited on export (PHP native Json)_
| [XSLX Spreadsheet](doc/WorkingWithExternalsFormats/XLSX.md) | :heavy_check_mark: | :heavy_check_mark: | _Optimized wrapper on top of [phpoffice/phpspreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)._ _It may also potentially be able to load older Excel formats by automatically detecting them; but this behavior is untested._
| [ODF Spreadsheet](doc/WorkingWithExternalsFormats/XLSX.md) | :heavy_check_mark: | :heavy_check_mark: | _Optimized wrapper on top of [phpoffice/phpspreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)_
| [HTML Table](doc/WorkingWithExternalsFormats/HtmlTable.md) | :x: | :heavy_check_mark: | _Limited_
| [PDO SQL](doc/WorkingWithExternalsFormats/PDOSql.md) | :heavy_check_mark: | :heavy_check_mark: | _Optimized_




### Extracting the underlying two-dimensional array:

```php
$myArray = $df->toArray();
print_r($myArray);
```

```php
[
    [0] => [
            [a] => 1
            [b] => 2
            [c] => 3
        ]

    [1] => [
            [a] => 4
            [b] => 5
            [c] => 6
        ]

    [2] => [
            [a] => 7
            [b] => 8
            [c] => 9
        ]
]
```

## Basic Operations

### Records

#### Add new records

```php
$df->addRecord([
    'a' => 42,
    'b' => 42,
]);

// equivalent to
$df[] = [
    'a' => 42,
    'b' => 42,
];


// Multiples records
$df->addRecords([
    [
    'a' => 42,
    'b' => 42,
    ],
    [
    'a' => 42,
    'b' => 43,
    ],
]);
```

#### Edit Record
```php
$df->updateRecord(
    position: 42,
    recordArray: [
      'a' => 42,
      'b' => 42,
    ]
);

// equivalent to
$df[42] = [
    'a' => 42,
    'b' => 42,
]);
```

#### Unset Record(s)
```php
$df->removeRecord(position: 42);

// equivalent
unset($df[42]);

// also equivalent
$df->filter(fn(array $record, int $position): bool => $position !== 42);
```


### Iterating overs records

#### Counting Records
Counting records:
```php
count($df);
$df->count(); // equivalent
```

#### Iterating over rows:
```php
foreach ($df as $key => $record) {
   echo $key.': '.implode('-', $record).PHP_EOL;
}
--------------------------
0: 1-2-3
1: 4-5-6
2: 7-8-9
```

### Columns

#### Add Column / Remove Column

Columns (attributes) are automatically created when a record contains them for the first time.
You can also create them manually at any time.

```php
$df->addColumn('a');
```

```php
$df->removeColumn('a');
$df->col('a')->remove(); // equivalent
```

#### Getting column name/objects
```php
$df->columnsNames()
--------------
[
    [0] => a
    [1] => b
    [2] => c
]
```

```php
$df->columns()
--------------
[
    [0] => #ColumnRepresentation Object
    [1] => #ColumnRepresentation Object
    [2] => #ColumnRepresentation Object
]
```

```php
$column = $df->col('a'); // return ColumnRepresentation object
$column->name; // 'a'
```

#### Rename Column
```php
$col = $df->col('colName')->rename('newName');

$col->name; // 'newName'
$col->getName(); // 'newName'
```

#### Get column as DataFrame
```php
$df->col('colName')->asDataFrame;

// equivalent to
$df->col('colName')->asDataFrame();
```

## The Select Statement

Create a statement containing 2 two columns, where columnB is > 42, limit to 100 rows but start à offset 10.

```php
$stmt = $df->select('column1','colum2')
    ->where(fn($record, $recordKey) => $record['columnB'] > 42)
    ->limit(100)
    ->offset(10);
```

Or select all column
```php
$stmt = $df->selectAll();
```

Complex where statement
```php
$stmt = $df->selectAll()->where(fn($r) => true)->and(...)->or(...)->or()->and();
```

is SQL equivalent to:
```sql
WHERE contition AND (condition OR condition OR condition) AND condition
```

Simpler Where clause
```php
$stmt = $df->selectAll()->whereColumnEqual('colA', 42);
$stmt = $df->selectAll()->whereKeyBetween(1, 42);
```

Statement are Traversable
```php
foreach($df->selectAll()->where(fn($r) => $r) as $recordKey => $record) {
    // ...
}
```

Get some stats _(non-exhaustive documentation)_
```php
$stmt->countRecords(); // count number of records in the statement

$stmt->count(); // count each value in selection
$stmt->countDistinct(); // count distinct value for of each records in statement
$stmt->size(); // count value in selection including null value
$stmt->sum(); // sum all numeric value of each records in statement
$stmt->mean(); // average numeric value in selection
$stmt->min(); $stmt->max(); // min / max value (numéric)
```

Return result as a new DataFrame object
```php
$newDf = $df->select('colA','colC')->whereColumnEqual('colB', 42)->get();
``````

Or directly to an array
```php
$newArr = $stmt->toArray();

// equivalent to (but slower)
$newArr = $stmt->get()->toArray();
```


## Advanced editions

### Applying functions to rows:
```php
$df = $df->apply(function ($row, $index) {
    $row['a'] = $row['c'] + 1;
    return $row;
});
```

### Applying functions to columns directly:
```php
$df->col('a')->apply(fn (mixed $value, int $position) => $value + 3);
```

### Set value for each record in column
```php
$df->col('a')->set(42);
```

### Set DataFrame (single column) to column
```php
$df->col('a')->set(new Dataframe([
    [1],
    [2],
    [3],
]));
```

### Set Column to Column
```php
$df->col('a')->set($df->col('b')->asDataFrame);
```


## Stats modules for a columns

### Natives Modules

#### Average
`sum / count` _where count of non empty and numeric properties_

```php
$df->col('a')->average();
$df->col('a')->average; # equivalent
```

#### Count
Count of non empty and numeric properties.

```php
$df->col('a')->count();
$df->col('a')->count; # equivalent
```

#### Sum
Where non empty and numeric properties

```php
$df->col('a')->sum();
$df->col('a')->sum; # equivalent
```

### Extend yourself
_TO DO_


## Types

### Convert data to a type (oneshoot)
```php
$df->col('a')->type(DataType::INT);
```

### Keep an active conversion for a column

#### Set it
```php
$df->col('a')->enforceType(DataType::INT);
```

#### Remove it
```php
$df->col('a')->enforceType(null); # Note that data already converted, only the following additions we be concerned.
```



Manipulating DataFrame using SQL:
```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$df = Builder::query($df, "

SELECT
  a,
  b
FROM dataframe
WHERE a = '4'
  OR b = '2';

");

print_r($df->toArray());
```

```php
Array
(
    [0] => Array
        (
            [a] => 1
            [b] => 2
        )

    [1] => Array
        (
            [a] => 4
            [b] => 5
        )

)
```

```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$df = Builder::query($df, "

UPDATE dataframe
SET a = c * 2;

");

print_r($df['a']->to_array());
```

```php
Array
(
    [0] => Array
        (
            [a] => 6
        )

    [1] => Array
        (
            [a] => 12
        )

    [2] => Array
        (
            [a] => 18
        )

)
```
