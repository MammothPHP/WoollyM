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

- [WoollyM: PHP Dataframes for Data Analysis library](#woollym-php-dataframes-for-data-analysis-library)
  - [Installation](#installation)
    - [Using Composer:](#using-composer)
    - [Requirements](#requirements)
  - [Instanciation (basic)](#instanciation-basic)
    - [Instantiating from an array:](#instantiating-from-an-array)
    - [Import or export from/to an external source](#import-or-export-fromto-an-external-source)
    - [Extracting the underlying two-dimensional array:](#extracting-the-underlying-two-dimensional-array)
  - [Basic Operations](#basic-operations)
    - [Records](#records)
      - [Add new records](#add-new-records)
      - [Edit Record](#edit-record)
      - [Unset Record(s)](#unset-records)
    - [Iterating overs records](#iterating-overs-records)
      - [Counting Records](#counting-records)
      - [Iterating over rows:](#iterating-over-rows)
    - [Columns](#columns)
      - [Add Column / Remove Column](#add-column--remove-column)
      - [Getting column name/objects](#getting-column-nameobjects)
      - [Rename Column](#rename-column)
      - [Get column as DataFrame](#get-column-as-dataframe)
    - [Data Overview](#data-overview)
      - [Head](#head)
  - [Logic and philosophy](#logic-and-philosophy)
  - [The Select Statement](#the-select-statement)
  - [Copy](#copy)
    - [Filter](#filter)
    - [Unique](#unique)
  - [Modifiers](#modifiers)
    - [Applying functions to rows:](#applying-functions-to-rows)
    - [Applying functions to a selection directly](#applying-functions-to-a-selection-directly)
    - [Set value for each record in column](#set-value-for-each-record-in-column)
    - [Set DataFrame (single column) to column](#set-dataframe-single-column-to-column)
    - [Set Column to Column](#set-column-to-column)
  - [Types Data](#types-data)
    - [Convert data to a type (oneshoot)](#convert-data-to-a-type-oneshoot)
    - [Keep an active conversion for a column](#keep-an-active-conversion-for-a-column)
      - [Set it](#set-it)
      - [Remove it](#remove-it)
  - [Manipulating Data using SQL](#manipulating-data-using-sql)


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

// Equivalent, but compatible with Iterable, DataFrame, Array
$otherDf = new DataFrame([
    [
    'a' => 42,
    'b' => 42,
    ],
    [
    'a' => 42,
    'b' => 43,
    ],
]);

$df->append($otherDf);
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
$df->hasColumn('a'); // true / false
$df->mustHaveColumn('a')->selectAll()... // Throw MammothPHP\WoollyM\Exceptions\InvalidSelectException or return $df
``

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
$newDf = $df->col('colName')->import();
```

### Data Overview

#### Head
```php
$arr = $df->head(length: 3);

// To Be
[
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]

$arr = $df->head(length: 3, offset: 1, columns:['a','c']);

// To Be
[
    ['a' => 4,  'c' => 6],
    ['a' => 7,  'c' => 9],
    ['a' => 10, 'c' => 12],
]
```

## Logic and philosophy

Three main access paths:
```php
$df->select('colNameA')->whereColumnEqual('colB', 42); // Return a new Select object
$df->copy()->unique(onColumns: 'colA'); // Return a new DataFrame contaning unique value from column A
$df->append($iterable); // Return $df (self)
```

* The `Select` object represent a statement to explore au subset of data correspondig to selection and doing stats with thems. You can build them using a SQL-like constructor. They offers some commodity helpers methods ton modify or copy directly the selected data, but it's not it's main purpose.
* The `Copy` object offer an API to return a NEW DataFrame without modify anything from the original DataFrame. It's also possible to export a Select object to a new dataFrame.
* Even if it's possible to modify a DataFrame using some methods from the Select object to apply to a selection. Most modifiers are directly accessible from the DataFrame object.


## The Select Statement

**Three variants:**
```php
$df->select('colA', 'colB'): Select // Return Select
$df->selectAll(): SelectAll // With all columns, and keep the * selection in returned select object even if columns are aded or deleted to the dataframe.
$df->col('colA'): ColunRepresentation // A classic select with extra methods to rename, remove, clone, type the selected column.
```

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
$newDf = $df->select('colA','colC')->whereColumnEqual('colB', 42)->export();
```

Or directly to an array
```php
$newArr = $stmt->toArray();

// equivalent to (but slower)
$newArr = $stmt->export()->toArray();
```

## Copy
> [!WARNING]
> Copy operations are not yet well optimized about memory consumption. Some of them have the potential to do so significantly in the future; others won't really be able to.

> [!NOTE]
> Clone the DataFrame then use equivalent modifier can be more efficient about memory consumtpion than the copy. Depending of the data-driver used and the PHP Copy-on-write feature. Wolly has a good PHP cloning support.

```php
// To a new Data Frame
$df->copy()->....

// To a custom dataFrame (useful for alternatives data-drivers)
$newDf = new DataFrame(dataDriver: $pdoSqlDriver);
$df->copy(to: $newDf)->...
```


### Filter
```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$df->filter(static function (array $row, int $key) {
    return $row['a'] > 4 || $row['a'] < 4;
});

expect($df->toArray())
    ->toBe([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
```

### Unique
```php
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
    ]);

    expect($df->copy()->unique('a')
        ->toArray())
        ->toBe([
            ['a' => 1],
            ['a' => 2],
            ['a' => 3],
        ]
    );

    expect($df->copy()->unique(['a', 'b'])
        ->toArray())
        ->toBe([
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 3],
            ['a' => 2, 'b' => 4],
            ['a' => 3, 'b' => 5],
        ]
    );
```

## Modifiers

### Applying functions to rows:
```php
$df->apply(function ($row, $index) {
    $row['a'] = $row['c'] + 1;
    return $row;
});
```

### Applying functions to a selection directly
```php
$df->col('a')->apply(fn (mixed $value, int $position) => $value + 3);
```

### Set value for each record in column
```php
$df->col('a')->set(42);
```

### Set DataFrame (single column) to column
```php
$df->col('a')->set(new Dataframe( [[1],[2],[3]] ));
```

### Set Column to Column
```php
$df->col('a')->set($df->col('b')->asDataFrame);
```


## Types Data

Two ways:
1. Oneshoot typing, converting existing data
2. Permanent typing, converting existing data
   1. And force futher data to be typed
   2. Or silently convert untyped futher data

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



## Manipulating Data using SQL

```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$df = Builder::query($df, " SELECT
                            a,
                            b
                            FROM dataframe
                            WHERE a = '4'
                            OR b = '2';
                        ");

print_r($df->toArray());
```

```php
[
    0 => ['a' => 1, 'b' => 2],
    1 => ['a' => 4, 'b' => 5]
]
```

```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$df = Builder::query($df, ' UPDATE dataframe
                            SET a = c * 2; ');

print_r($df['a']->to_array());
```

```php
[
    0 => ['a' => 6],
    1 => ['a' => 12],
    2 => ['a' => 18]
]
```
