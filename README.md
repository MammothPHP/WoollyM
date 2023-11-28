<p align="center">
    <picture>
        <img alt="" width="40%" src="woollym_logo.svg">
    </picture>
</p>

# WoollyM: PHP Dataframes for Data Analysis library

WoollyM is a PHP library designed to make working with tabular/relational data, files, and databases easy. The core component of the library is the DataFrame class - a tabular data structure that raises the level of abstraction when working with tabular, two-dimensional data.

## Installation

### Using Composer:

```sh
composer require mammothphp/woollym
```

### Requirements
 - PHP 8.3 or higher
 - php_mbstring extension

### License
 - [BSD-3-Clause](http://opensource.org/licenses/BSD-3-Clause)

## Data Format Examples

### Instantiating from an array:

```php
$df = DataFrame::fromArray([
    ['a' => 1, 'b' => 2, 'c' => 3],
    ['a' => 4, 'b' => 5, 'c' => 6],
    ['a' => 7, 'b' => 8, 'c' => 9],
]);
```


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
]);


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
unset($df[42]); // equivalent

// equivalent to
$df->filter(fn(array $record, int $position): bool => $position !== 42);
```


### Itarating overs records

#### Counting Records
Counting records:
```php
count($df);
$df->count(); // equivalent
```

#### Iterating over rows:
```php
foreach ($df as $i => $row) {
   echo $i.': '.implode('-', $row).PHP_EOL;
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
$column = $df->col('a')
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
$df->col('a')->apply(fn (mixed $value, int $position) => $el + 3);
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

$df = $df->query("

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

$df = $df->query("

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
