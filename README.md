<p align="center">
    <picture>
        <img alt="" width="40%" src="logos/woollym_logo.png">
    </picture>
</p>

> Main Author: [Julien Boudry](https://www.linkedin.com/in/julienboudry/)  
> License: [BSD-3](LICENSE.txt) - _Please [say hello](https://github.com/MammothPHP/WoollyM/discussions/categories/your-woolly-projects) if you like or use this code!_  
> Contribute: [Contribute File](CONTRIBUTING.md)   
> Donation: ₿ [bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh](https://blockchair.com/bitcoin/address/bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh) or [Github Sponsor Page](https://github.com/sponsors/julien-boudry)  
> _You can also offer me a bottle of good wine._  

> [!WARNING]
> This project is currently at an experimental stage. Production use is not recommended. APIs and functionalities are subject to change at any time without notice. Documentation is still deficient. Help and feedback are most welcome.

> [!NOTE]
> _Woolly was a fork from [archon/dataframe](https://github.com/hwperkins/Archon) project which was very useful and inspiring during development. Today, the internal engine has been almost completely rewritten and the public APIs are radically different and incompatible. A few traces of code and ideas remain, they have been placed by their original author under the BSD-3 license._

# WoollyM: PHP Dataframes for Data Analysis library

WoollyM is a PHP library for data analysis. It can be used to represent tabular data from various sources _(CSV, database, JSON, Excel...)_. The unified API can then be used easily to browse, analyze, modify, and export data in a variety of formats, we try to provide a very playful, modern, expressive, and user-friendly interface. This API is also modular and extensible, so you can easily add your own calculation and exploration methods.

Performances are optimized to be as light as possible on RAM during operations (input, output, read, write, stats, copy, clone), this is done using - internally - complex iterators and optimization preferring RAM over speed (even if we try to be fast also). The storage engine uses a modular storage system, if the default PhpArray driver uses RAM, the use of a database driver (such as the PDO driver) theoretically allows you to work on extremely large datasets. Using appropriate drivers, you can also write - for example - directly to the database (add, update) using the Woolly API.

- [WoollyM: PHP Dataframes for Data Analysis library](#woollym-php-dataframes-for-data-analysis-library)
  - [Installation](#installation)
    - [Using Composer:](#using-composer)
    - [Requirements](#requirements)
    - [Namespace](#namespace)
  - [Note on architecture](#note-on-architecture)
  - [Principles](#principles)
  - [Instantiation (basic)](#instantiation-basic)
    - [Instantiating from an array:](#instantiating-from-an-array)
    - [Import or export from/to an external source](#import-or-export-fromto-an-external-source)
    - [Extracting the underlying two-dimensional array:](#extracting-the-underlying-two-dimensional-array)
  - [Basic Operations](#basic-operations)
    - [Records](#records)
      - [Add new records](#add-new-records)
      - [Edit Record](#edit-record)
      - [Unset Record(s)](#unset-records)
    - [Iterating over records](#iterating-over-records)
      - [Counting Records](#counting-records)
      - [Iterating over rows:](#iterating-over-rows)
    - [Columns](#columns)
      - [Add Column / Remove Column](#add-column--remove-column)
      - [Getting column name/objects](#getting-column-nameobjects)
      - [Rename Column](#rename-column)
      - [Get column as DataFrame](#get-column-as-dataframe)
    - [Data Overview](#data-overview)
      - [Head](#head)
  - [Logic and Philosophy](#logic-and-philosophy)
  - [The Select Statement](#the-select-statement)
    - [The three different types of Select statements](#the-three-different-types-of-select-statements)
    - [Filter \& Limit the Select statements](#filter--limit-the-select-statements)
    - [Copy from a Select Statement](#copy-from-a-select-statement)
    - [Aggregate stats function](#aggregate-stats-function)
  - [Copy](#copy)
    - [Modification to a Selection](#modification-to-a-selection)
      - [Applying functions to a selection directly](#applying-functions-to-a-selection-directly)
      - [Set a value for each record in a column](#set-a-value-for-each-record-in-a-column)
      - [Set DataFrame (single column) to a column](#set-dataframe-single-column-to-a-column)
      - [Set Column to a Column](#set-column-to-a-column)
    - [Filter](#filter)
    - [Unique](#unique)
  - [Update](#update)
      - [Applying functions to each row](#applying-functions-to-each-row)
      - [preg\_replace](#preg_replace)
      - [applyIndexMap](#applyindexmap)
  - [Delete](#delete)
      - [Execute](#execute)
      - [Filter](#filter-1)
  - [Sorting](#sorting)
      - [sortRecordsByColumns](#sortrecordsbycolumns)
      - [sortColumn](#sortcolumn)
  - [Types Data](#types-data)
    - [Convert data to a type (one shot)](#convert-data-to-a-type-one-shot)
    - [Keep an active conversion for a column](#keep-an-active-conversion-for-a-column)
      - [Set it](#set-it)
      - [Remove it](#remove-it)
  - [Manipulating Data using SQL](#manipulating-data-using-sql)
    - [Copy DataFrame from SQL](#copy-dataframe-from-sql)
  - [Use Data Driver to explore external sources or to overcome technical limitations on major datasets](#use-data-driver-to-explore-external-sources-or-to-overcome-technical-limitations-on-major-datasets)
    - [Natively provided drivers](#natively-provided-drivers)
    - [Aggregate Function optimized on driver side (performance)](#aggregate-function-optimized-on-driver-side-performance)
    - [Use specific drivers (PdoSql example)](#use-specific-drivers-pdosql-example)
    - [Implement your custom drivers for WoollyM](#implement-your-custom-drivers-for-woollym)
- [Create custom aggregation functions](#create-custom-aggregation-functions)


## Installation

### Using Composer:
```sh
composer require mammothphp/woollym
```

### Requirements
 - PHP 8.3 or higher
 - php_mbstring extension

### Namespace
```php
use MammothPHP\WoollyM\DataFrame;
```

## Note on architecture
- Woolly is extendable, or at least he was trying to get used to the idea.
- Data are stored in `data-drivers` that are modules. Currently Woolly offer 2 natives drivers _(the default PhpArray and PdoSql)_ but you can create your own _(without fork)_. Drivers can limit some functionnality but they don't change the public API.
- Statements aggregate function _(like sum, count, max...)_ are modules, most current as offer natively, but you can had your own _(without fork)_
- `Builder` API is used to to create or export a DataFrame from an external sources _(file, database, string...)_ This results in things like  `$df = XLSX::fromFile($path)->import()`.
  - To keep the API as cute as possible for the most common cases, this is different for import/export with an `Array` (or `Traversable`).
  - Builder are simple (and optimized) wrapper on top of DataFrame. You can create your own builder _(using the asbtract class `Builder` or not)_.
  - Builders should not be confused with `data-drivers`, as the associated methods are always distinct. For example, the PDO Builder will not create a DataFrame using the PdoSql driver to browse or modify the underlying database. In this case, you need to specify the DataFrame object (using the needed driver) in wich to import.

## Principles

- A `Record` is similar to a line in a spreadsheet.
    - A `Record` can contain from none _(empty record)_ to several `Columns`.
    - The default behavior of API is to return no entry for non-existent columns in the `Record`. But options allow you to virtually return a NULL value if necessary.
    - Each record has a unique key. Generated on the Woolly side _(more specifically by the data-driver used)_. Currently only integer are supported. The default driver (PhpArray) index key from 0.
    - Each record `Column` (property) contain a value. Can be of any PHP type (including `null`) on the default data-driver, others drivers can limit types of silently convert it.
- A `Column` represents a property common to several records, and is similar to a column in a spreadsheet.
  - `Columns` can be added manually or dynamically (a new `Record` can contains a new column) at any time.
  - `Columns` are represented to the user by case-sensitive string names.


## Instantiation (basic)

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
_To limit external dependencies, some modules could require a separate `composer require`. Please consult the instructions for each of the needed modules.__ 

> [Import/Export modules documentation and examples](doc/WorkingWithExternalsFormats)

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

// equivalent to
$df->insert()->record([
    'a' => 42,
    'b' => 42,
]);


// Multiples records from array, dataFrame or others iterables
$df->insert()->append([
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
    key: 42,
    recordArray: [
      'a' => 42,
      'b' => 42,
    ]
);

// equivalent to
$df[42] = [
    'a' => 42,
    'b' => 42,
];

// equivalent to
$df->update()->record(
    key: 42,
    recordArray: [
      'a' => 42,
      'b' => 42,
    ]
);
```

#### Unset Record(s)
```php
$df->removeRecord(key: 42);

// equivalent
unset($df[42]);

// equivalent
$df->delete()->record(key: 42);

// also equivalent
$df->delete()->filter(fn(array $record, int $position): bool => $position === 42);
```



### Iterating over records

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

## Logic and Philosophy

Accessing data or modifying it, use an Sql inspired syntax using the keyword `select()`, `insert()`, `update()`, `delete()`, `copy()` as a prefix to corresponding methods.

```php
$df->select('colNameA')->whereColumnEqual('colB', 42); // Return a new Select statement object
$df->insert()->append($iterable); // Return $df (self)
$df->update()->record(key: 42, $recordArray); // Return $df (self)
$df->delete()->whereColumnEqual('colA', 'foo')->execute(); // Return $df (self)

$df->copy()->unique(onColumns: 'colA'); // Return a new DataFrame contaning unique value from column A
```


## The Select Statement
The `Select` object represents a statement to explore au subset of data corresponding to selection and doing stats with them. You can build them using a SQL-like constructor. They offer some commodity helpers methods to modify or copy directly the selected data, but it's not its main purpose.

### The three different types of Select statements
```php
$df->select('colA', 'colB'): Select // Return Select
$df->selectAll(): SelectAll // With all columns, and keep the  selection in returned select object even if columns are aded or deleted to the dataframe.
$df->col('colA'): ColunRepresentation // A classic select with extra methods to rename, remove, clone, type the selected column.
```

### Filter & Limit the Select statements
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

### Copy from a Select Statement

Return the result as a new DataFrame object:
```php
$newDf = $df->select('colA','colC')->whereColumnEqual('colB', 42)->export();
```

Or directly to an array:
```php
$newArr = $stmt->toArray();

// equivalent to (but slower)
$newArr = $stmt->export()->toArray();
```

### Aggregate stats function
_(non-exhaustive documentation)_

```php
$stmt = $df->selectAll();

$stmt->countRecords(); // count number of records in the statement
$stmt->count(); // count each value in selection
$stmt->countDistinct(); // count distinct value for of each records in statement
$stmt->size(); // count value in selection including null value
$stmt->sum(); // sum all numeric value of each records in statement
$stmt->mean(); // average numeric value in selection
$stmt->min(); // min value (numeric)
$stmt->max(); // max value (numeric)
```

## Copy
> [!WARNING]
> Copy operations are not yet well optimized about memory consumption. Some of them have the potential to do so significantly in the future; others won't really be able to.

> [!NOTE]
> Clone the DataFrame then use equivalent modifier can be more efficient about memory consumtpion than the copy. Depending of the data-driver used and the PHP Copy-on-write feature. Woolly has a good PHP cloning support.

 The `Copy` object offers an API to return a NEW DataFrame without modifying anything from the original DataFrame. It's also possible to export a Select object to a new DataFrame, but Copy API also offer transformations methods.

```php
// To a new Data Frame
$df->copy()->....

// To a custom dataFrame (useful for alternatives data-drivers)
$newDf = new DataFrame(dataDriver: $pdoSqlDriver);
$df->copy(to: $newDf)->...
```


### Modification to a Selection

#### Applying functions to a selection directly
```php
$closure = fn (mixed $value, int $position) => $value + 3;

$df->col('a')->apply($closure);

// Equivalent to
$df->update('a')->apply($closure);
```

#### Set a value for each record in a column
```php
$df->col('a')->set(42);
```

#### Set DataFrame (single column) to a column
```php
$df->col('a')->set(new Dataframe( [[1],[2],[3]] ));
```

#### Set Column to a Column
```php
$df->col('a')->set($df->col('b')->asDataFrame);
```


### Filter
```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$newDf = $df->copy()->filter(static function (array $record, int $key) {
    return $record['a'] > 4 || $record['a'] < 4;
});

expect($newDf->toArray())
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

## Update

#### Applying functions to each row
```php
$df->update()->apply(function ($record, $index) {
    $record['a'] = $record['c'] + 1;
    return $record;
});
```

#### preg_replace
```php
    $df = new DataFrame([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
    
    $df->update()->preg_replace('/[1-5]/', 'foo');

    $df->toArray();
    // To Be:
    [
        ['a' => 'foo', 'b' => 'foo', 'c' => 'foo'],
        ['a' => 'foo', 'b' => 'foo', 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];
```

#### applyIndexMap
Apply new values to specific rows of the DataFrame using row index.

If column is supplied, will apply to column.
If column is absent, will apply to row.

Source:
```php
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
```

By column:
```php
    $df->update()->applyIndexMap(
        map: [
            0 => 'foo',
            1 => fn($oldValue) => $oldValue * 2,
            2 => 'baz',
        ],
        column: 'a'
    );

    $df->toArray();
    // To Be:
    [
        ['a' => 'foo', 'b' => 2, 'c' => 3],
        ['a' => 8, 'b' => 5, 'c' => 6],
        ['a' => 'baz', 'b' => 8, 'c' => 9],
    ]
```

By row:
```php
    $df->update()->applyIndexMap(
        map: [
            1 => fn(array $oldRecord): array => array_map(fn(int $v): int => $v * 2, $oldRecord),
            2 => [ 'a' => 1, 'b' => 2, 'c' => 3 ],
        ]
    );

    $df->toArray();
    //To Be:
    [
        0 => ['a' => 1, 'b' => 2, 'c' => 3],
        1 => ['a' => 8, 'b' => 10, 'c' => 12],
        2 => ['a' => 1, 'b' => 2, 'c' => 3],
    ]
```


## Delete

#### Execute
```php
    $df = DataFrame::fromArray([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
        ['colA' => 10, 'colB' => 11, 'colC' => 12],
        ['colA' => 13, 'colB' => 14, 'colC' => 15],
    ]);
    
    $df->delete()
        ->whereColumnEqual('colB', 5)
        ->or(fn (array $record): bool => $record['colA'] >= 10)
        ->execute();

    $df->toArray();
    // To Be:
    [
        0 => ['colA' => 1, 'colB' => 2, 'colC' => 3],
        2 => ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]
```

#### Filter
```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$df->delete()->filter(static function (array $record, int $recordKey) {
    return $record['a'] > 4 || $record['a'] < 4;
});

$df->toArray();
// To Be:
[
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 7, 'b' => 8, 'c' => 9],
];
```

## Sorting
_Sorting can be incompatible with some drivers. And some of them could be accelerated by some compatible drivers._

#### sortRecordsByColumns
Sort record ascending / descing by column(s).
```php
$df->sortRecordByColumns(by: 'colA', ascending: true);
$df->sortRecordByColumns(by: ['colA', 'colB'], ascending: true); // By colA then colB
```

#### sortColumn
Rearranges the order of columns by column name. Takes as argument a Clusure that functions identically to the native PHP uasort() api.
If no callback has been supplied, then it's will arrange it using alphabetics order.

```php
    $df->sortColumns(fn(string $a, string $b): int => $a <=> $b);
```

## Types Data
> [!WARNING]
> Type's system is unsatisfactory and will certainly be totally rethought in the future.

Two ways:
1. Converts pre-existing data once only 
2. Converts pre-existing data and forces the type of future data
   1. And force the future data to be typed since submission
   2. Or silently convert untyped future data

### Convert data to a type (one shot)
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

### Copy DataFrame from SQL
```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);

$resultingDf = $df->copy()->fromSqlQuery(" SELECT
                        a,
                        b
                        FROM dataframe
                        WHERE a = '4'
                        OR b = '2';
                    ");

// $resultingDf To Be:
[
    0 => ['a' => 1, 'b' => 2],
    1 => ['a' => 4, 'b' => 5]
]
```


## Use Data Driver to explore external sources or to overcome technical limitations on major datasets
> [!WARNING]
> Non-default data drivers are still highly experimental and unfinished. The drivers interface and API will also be modified.

### Natively provided drivers
|Driver|Comment|Memory Usage|Perf. (write access)|Perf. (random access)|Perf. (mass read)|Aggregate Functions
|---|---|---|---|---|---|---
| PhpArray | The default driver | Maximal, all the data stay in ram memory. PHP opy-on-write capacity are limited in context | Very Fast | Very Fast | Vert Fast | Very Fast | Moderately slow, some of them can cause huge memory usage (unique value...)
| PdoSql | _(Experimental)_ Interacting with a database with any PHP PDO driver available | Very low, theoritically infinite | Slow | Very Slow | Medium | Slow (some function can be further optimized)

### Aggregate Function optimized on driver side (performance)
__NOT YET IMPLEMENTED__


### Use specific drivers (PdoSql example)
```php
$tableName = 'testTable';
$primaryKey = 'id';

$pdo = new PDO('sqlite::memory:');
$pdo->exec("CREATE TABLE  {$tableName}  ({$primaryKey} INTEGER PRIMARY KEY, a TEXT, b TEXT, c TEXT);");

$PdoDriver = new PdoSql(db: $pdo, table: $tableName, keyColumn: $primaryKey);

$df = new DataFrame(dataDriver: $PdoDriver);
```


### Implement your custom drivers for WoollyM
__TODO__


# Create custom aggregation functions

Example with a basic size function counting each field in records from a statement.

```php
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Stats\Modules;
use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

Modules::registerModule(
    new class implements StatsMethodInterface, StatsPropertyInterface
    {
        public const string NAME = 'size';

        public function executeProperty(Select $select): int
        {
            return $this->execute($select);
        }

        public function executeMethod(Select $select, array $arguments): int
        {
            return $this->execute($select, ...$arguments);
        }

        protected function execute(Select $select): int
        {
            $r = 0;

            foreach ($select as $record) {
                $r += count($record);
            }

            return $r;
        }
    }
);


// Use it!

$df = new DataFrame([
    ['colA' => 42, 'colB' => 7 , 'colC' => 8],
    ['colA' => 77, 'colB' => 7 , 'colC' => 42],
    ['colA' => 77, 'colB' => 7 , 'colC' => 8],
    ['colA' => 42, 'colB' => 7 , 'colC' => 42],
    ['colA' => 77, 'colB' => 7 , 'colC' => 8],
]);

$df->select('colA', 'colC')->whereColumnEqual('colA', 42)->size(); // 4
```
