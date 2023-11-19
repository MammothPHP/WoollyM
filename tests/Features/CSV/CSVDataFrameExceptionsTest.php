<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{FileExistsException, InvalidSelectException, UnknownOptionException};

test('overwrite fail c s v', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVOverwrite.csv';

    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $df->toCSV($fileName);
})->throws(FileExistsException::class);

test('invalid option', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVOverwrite.csv';

    DataFrame::fromCSV($fileName, ['invalid_option' => 0]);
})->throws(UnknownOptionException::class);

test('unknown delimiter', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVUnknownDelimiter.csv';

    DataFrame::fromCSV($fileName);
})->throws(RuntimeException::class);

test('invalid column count', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVInvalidColumnCount.csv';

    DataFrame::fromCSV($fileName);
})->throws(InvalidSelectException::class);
