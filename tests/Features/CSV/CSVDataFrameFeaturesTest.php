<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;

test('from c s v', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV($fileName);

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
    ]);
});

test('from c s v dirty', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVdirty.csv';

    $df = DataFrame::fromCSV($fileName, [
        'include' => '/^([1-9]|a)/',
        'exclude' => '/^([7]|junk)/',
    ]);

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
    ]);
});

test('from c s v no header', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV($fileName, ['columns' => ['x', 'y', 'z']]);

    expect($df->toArray())->toEqual([
        ['x' => 'a', 'y' => 'b', 'z' => 'c'],
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
    ]);
});

test('from c s vcol map', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV($fileName, [
        'colmap' => [
            'a' => 'x',
            'b' => 'y',
            'c' => 'z',
        ],
    ]);

    expect($df->toArray())->toEqual([
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
    ]);
});

test('c s v mapping alias', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df1 = DataFrame::fromCSV($fileName, [
        'colmap' => [
            'a' => 'x',
            'b' => 'y',
            'c' => 'z',
        ],
    ]);

    $df2 = DataFrame::fromCSV($fileName, [
        'mapping' => [
            'a' => 'x',
            'b' => 'y',
            'c' => 'z',
        ],
    ]);

    expect($df2->toArray())->toEqual($df1->toArray());
});

test('from c s vcol map to null', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV($fileName, [
        'colmap' => [
            'a' => 'x',
            'b' => null,
            'c' => 'z',
        ],
    ]);

    expect($df->toArray())->toEqual([
        ['x' => 1, 'z' => 3],
        ['x' => 4, 'z' => 6],
    ]);
});

test('from c s vcol map to null2', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV($fileName, [
        'colmap' => [
            'a' => 'x',
            'b' => null,
            'c' => 'z',
            'doesnt_exist' => 'b',
            'doesnt_exist_either' => null,
        ],
    ]);

    expect($df->toArray())->toEqual([
        ['x' => 1, 'z' => 3],
        ['x' => 4, 'z' => 6],
    ]);
});

test('save c s v', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVSave.csv';
    if (file_exists($fileName)) {
        unlink($fileName);
    }

    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $q = '"';
    $df->toCSV($fileName, ['quote' => $q]);

    $data = file_get_contents($fileName);

    if (file_exists($fileName)) {
        unlink($fileName);
    } else {
        $this->fail("File should exist but does not: {$fileName}");
    }

    $expected = "a,b,c\n1,2,3\n4,5,6\n7,8,9\n";

    expect($data)->toEqual($expected);
});
