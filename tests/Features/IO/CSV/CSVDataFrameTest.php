<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use League\Csv\{Reader, Writer};
use MammothPHP\WoollyM\Exceptions\DataFrameException;
use MammothPHP\WoollyM\IO\{CSV, TSV};

test('from csv', function (Closure $file): void {
    $expected = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
    ];

    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';
    $input = $file($fileName);

    $csv = match (true) {
        \is_resource($input) => CSV::fromStream($input),
        $input instanceof SplFileInfo => CSV::fromFileInfo($input),
        $input instanceof Reader => CSV::fromCsvReader($input),
        file_exists($input) => CSV::fromFilePath($input),
        \is_string($input) => CSV::fromString($input),
        default => throw new Exception('Not supported input')
    };

    $df = $csv->import();

    expect($df->toArray())->toEqual($expected);
})->with([
    'file path' => fn(string $fileName): string => $fileName,
    'stream' => fn(string $fileName) => fopen($fileName, 'r'),
    'reader' => fn(string $fileName): Reader => Reader::createFromPath($fileName)->setHeaderOffset(0),
    'spl file info' => fn(string $fileName): SplFileInfo => new SplFileInfo($fileName),
    'string' => fn(string $fileName): string => file_get_contents($fileName),
]);

test('from csv no header', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = CSV::fromFilePath($fileName)->format(
        headerOffset: null,
        columns: ['x', 'y', 'z']
    )->import();

    expect($df->toArray())->toEqual([
        ['x' => 'a', 'y' => 'b', 'z' => 'c'],
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
    ]);
});

test('from csvcol map', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = CSV::fromFilePath($fileName)->format(mapping: [
        'a' => 'x',
        'b' => 'y',
        'c' => 'z',
    ])->import();

    expect($df->toArray())->toEqual([
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
    ]);
});

test('csv mapping alias', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df1 = CSV::fromFilePath($fileName)->format(mapping: [
        'a' => 'x',
        'b' => 'y',
        'c' => 'z',
    ])->import();

    $df2 = DataFrame::fromArray([
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
    ]);

    expect($df1->toArray())->toEqual($df2->toArray());
});

test('from csvcol map to null', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $df = CSV::fromFilePath($fileName)->format(mapping: [
        'a' => 'x',
        'b' => null,
        'c' => 'z',
    ])->import();

    expect($df->toArray())->toEqual([
        ['x' => 1, 'z' => 3],
        ['x' => 4, 'z' => 6],
    ]);
});

test('from csvcol map to null2', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSV.csv';

    $mapping = [
        'a' => 'x',
        'b' => null,
        'c' => 'z',
        'doesnt_exist' => 'b',
        'doesnt_exist_either' => null,
    ];

    $df = CSV::fromFilePath($fileName)->format(mapping: $mapping)->import();

    expect($df->toArray())->toEqual([
        ['x' => 1, 'z' => 3],
        ['x' => 4, 'z' => 6],
    ]);
});

test('from tsv', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testTSV.tsv';

    $df = TSV::fromFilePath($fileName)->import();

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
    ]);
});

test('save csv', function (Closure $file): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVSave.csv';

    if (file_exists($fileName)) {
        unlink($fileName);
    }

    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'c' => 6, 'b' => 5],
        ['a' => 7, 'b' => 'hui,t', 'c' => 9],
    ]);

    $input = $file($fileName);

    if (\is_resource($input)) {
        CSV::fromDataFrame($df)->toStream(phpStream: $input, writeHeader: true);
    } else {
        CSV::fromDataFrame($df)->toFile(file: $input, overwriteFile: true, writeHeader: true);
    }

    $data = file_get_contents($fileName);

    if (file_exists($fileName)) {
        unlink($fileName);
    } else {
        $this->fail("File should exist but does not: {$fileName}");
    }

    $expected = "a,b,c\n" .
                "1,2,3\n" .
                "4,5,6\n" .
                "7,\"hui,t\",9\n";

    expect($data)->toBe($expected);
})->with([
    'file path' => fn(string $fileName): string => $fileName,
    'stream' => fn(string $fileName) => fopen($fileName, 'w+'),
    'writer' => fn(string $fileName): Writer => Writer::createFromPath($fileName, 'w+'),
]);

test('save csv with traps', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'c' => 3],
        ['a' => 4, 'c' => 6, 'b' => 5],
        ['b' => 'hui;t'],
    ]);

    // Unordered columns
    $tempFile = new SplTempFileObject;

    CSV::fromDataFrame($df)->format(delimiter: ';')->toFile(file: $tempFile, overwriteFile: true, writeHeader: true);

    $expected = "a;c;b\n" .
                "1;3;\n" .
                "4;6;5\n" .
                ";;\"hui;t\"\n";

    $tempFile->rewind();
    expect($tempFile->fread(1024))->toBe($expected);

    // Sort Columns
    $df->sortColumns();

    $tempFile = new SplTempFileObject;
    CSV::fromDataFrame($df)->toFile(file: $tempFile, overwriteFile: true, writeHeader: true);

    $expected = "a,b,c\n" .
                "1,,3\n" .
                "4,5,6\n" .
                ",hui;t,\n";

    $tempFile->rewind();
    expect($tempFile->fread(1024))->toBe($expected);
});

test('save to invalid file', function (): void {
    CSV::fromDataFrame(new DataFrame)->toFile(file: __FILE__, overwriteFile: false);
})->throws(DataFrameException::class);

test('csv to string', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'c' => 6, 'b' => 5],
        ['a' => 7, 'b' => 'hui,t', 'c' => 9],
    ]);

    $csv = CSV::fromDataFrame($df)->format(enclosure: "'")->toString();

    expect($csv)->toBe(
        "a,b,c\n" .
        "1,2,3\n" .
        "4,5,6\n" .
        "7,'hui,t',9\n"
    );
});
