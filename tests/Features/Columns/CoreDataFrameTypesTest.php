<?php

declare(strict_types=1);
use MammothPHP\WoollyM\{DataFrame, DataType};

test('convert numeric integer', function (): void {
    $df = DataFrame::fromArray([
        ['numeric' => 1,              'integer' => 3.14],
        ['numeric' => -4,             'integer' => '$5.23'],
        ['numeric' => 7.23,           'integer' => '8x'],
        ['numeric' => .3,             'integer' => '8-'],
        ['numeric' => '$3,000,000.4', 'integer' => 'asdf'],
        ['numeric' => '3.0-',         'integer' => '$3,456,789.23'],
    ]);

    $df->update()->convertTypes([
        'numeric' => DataType::NUMERIC,
        'integer' => DataType::INT,
    ]);

    foreach ($df as $row) {
        expect($row['numeric'])->toBeNumeric();
        expect($row['integer'])->toBeInt();
    }

    expect($df->toArray())->toBe([
        ['numeric' => 1,            'integer' => 3],
        ['numeric' => -4,           'integer' => 5],
        ['numeric' => 7.23,         'integer' => 8],
        ['numeric' => 0.3,          'integer' => -8],
        ['numeric' => 3000000.4,    'integer' => 0],
        ['numeric' => -3.0,         'integer' => 3456789],
    ]);
});

test('convert date time', function (): void {
    $df = DataFrame::fromArray([
        ['datetime' => '12/03/1996'],
        ['datetime' => '03-2001-04'],
        ['datetime' => 'Jun 04 2010'],
        ['datetime' => ''],
    ]);

    $df->update()->convertTypes([
        'datetime' => DataType::DATETIME,
    ], ['d/m/Y', 'd-Y-m', 'M d Y'], 'Y-m-d');

    expect($df->toArray())->toBe([
        ['datetime' => '1996-03-12'],
        ['datetime' => '2001-04-03'],
        ['datetime' => '2010-06-04'],
        ['datetime' => '0001-01-01'],
    ]);

    $df->update()->convertTypes([
        'datetime' => DataType::DATETIME,
    ], 'Y-m-d', 'M d Y');

    expect($df->toArray())->toBe([
        ['datetime' => 'Mar 12 1996'],
        ['datetime' => 'Apr 03 2001'],
        ['datetime' => 'Jun 04 2010'],
        ['datetime' => 'Jan 01 0001'],
    ]);

    $this->expectExceptionMessage("Error parsing date string 'Mar 12 1996' with date format Y-m-d");
    $df->update()->convertTypes([
        'datetime' => DataType::DATETIME,
    ], 'Y-m-d', 'Y-m-d');
});

test('convert currency a c c o u n t i n g', function (): void {
    $df = DataFrame::fromArray([
        ['currency' => '1',          'accounting' => '1'],
        ['currency' => '-123456789', 'accounting' => '-123456789'],
        ['currency' => '',           'accounting' => ''],
        ['currency' => '123.45',     'accounting' => '123.45'],
        ['currency' => 'asdf',       'accounting' => 'asdf'],
        ['currency' => 'asdf.56-',   'accounting' => 'asdf.56-'],
    ]);

    $df->update()->convertTypes([
        'currency'   => DataType::CURRENCY,
        'accounting' => DataType::ACCOUNTING,
    ]);

    expect($df->toArray())->toBe([
        ['currency' => '$1.00',            'accounting' => '$1.00'],
        ['currency' => '-$123,456,789.00', 'accounting' => '$(123,456,789.00)'],
        ['currency' => '$0.00',            'accounting' => '$0.00'],
        ['currency' => '$123.45',          'accounting' => '$123.45'],
        ['currency' => '$0.00',            'accounting' => '$0.00'],
        ['currency' => '-$0.56',           'accounting' => '$(0.56)'],
    ]);
});
