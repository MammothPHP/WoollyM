<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;

test('from csv', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV($fileName);

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
    ]);
});

test('from csv no header', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV(input: $fileName, headerOffset: null, columns: ['x', 'y', 'z']);

    expect($df->toArray())->toEqual([
        ['x' => 'a', 'y' => 'b', 'z' => 'c'],
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
    ]);
});

test('from csvcol map', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV(
        input: $fileName,
        mapping: [
            'a' => 'x',
            'b' => 'y',
            'c' => 'z',
        ],
    );

    expect($df->toArray())->toEqual([
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
    ]);
});

test('csv mapping alias', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df1 = DataFrame::fromCSV(
        input: $fileName,
        mapping: [
            'a' => 'x',
            'b' => 'y',
            'c' => 'z',
        ],
    );

    $df2 = DataFrame::fromCSV(
        input: $fileName,
        mapping: [
            'a' => 'x',
            'b' => 'y',
            'c' => 'z',
        ],
    );

    expect($df2->toArray())->toEqual($df1->toArray());
});

test('from csvcol map to null', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV(
        input: $fileName,
        mapping: [
            'a' => 'x',
            'b' => null,
            'c' => 'z',
        ],
    );

    expect($df->toArray())->toEqual([
        ['x' => 1, 'z' => 3],
        ['x' => 4, 'z' => 6],
    ]);
});

test('from csvcol map to null2', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = DataFrame::fromCSV(
        input: $fileName,
        mapping: [
            'a' => 'x',
            'b' => null,
            'c' => 'z',
            'doesnt_exist' => 'b',
            'doesnt_exist_either' => null,
        ],
    );

    expect($df->toArray())->toEqual([
        ['x' => 1, 'z' => 3],
        ['x' => 4, 'z' => 6],
    ]);
});

test('from tsv', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testTSV.tsv';

    $df = DataFrame::fromTSV($fileName);

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
    ]);
});

test('save csv', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVSave.csv';

    if (file_exists($fileName)) {
        unlink($fileName);
    }

    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'c' => 6, 'b' => 5],
        ['a' => 7, 'b' => 'hui,t', 'c' => 9],
    ]);

    $df->toCSV($fileName, overwrite: true, writeHeader: true);

    $data = file_get_contents($fileName);

    if (file_exists($fileName)) {
        unlink($fileName);
    } else {
        $this->fail("File should exist but does not: {$fileName}");
    }

    $expected = "a,b,c\n1,2,3\n4,5,6\n7,\"hui,t\",9\n";

    expect($data)->toBe($expected);
});
