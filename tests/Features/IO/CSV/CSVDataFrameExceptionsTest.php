<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\FileExistsException;
use MammothPHP\WoollyM\IO\CSV;

test('overwrite fail csv', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testCSVOverwrite.csv';

    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    CSV::fromDataFrame($df)->toFile($fileName);
})->throws(FileExistsException::class);
