<?php

declare(strict_types=1);
use CondorcetPHP\Oliphant\DataFrame;

test('overwrite fail c s v', function (): void {
    $fileName = __DIR__.\DIRECTORY_SEPARATOR.'TestFiles'.\DIRECTORY_SEPARATOR.'testCSVOverwrite.csv';

    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $this->expectException('CondorcetPHP\Oliphant\Exceptions\FileExistsException');
    $df->toCSV($fileName);
});

test('invalid option', function (): void {
    $fileName = __DIR__.\DIRECTORY_SEPARATOR.'TestFiles'.\DIRECTORY_SEPARATOR.'testCSVOverwrite.csv';

    $this->expectException('CondorcetPHP\Oliphant\Exceptions\UnknownOptionException');
    DataFrame::fromCSV($fileName, ['invalid_option' => 0]);
});

test('unknown delimiter', function (): void {
    $fileName = __DIR__.\DIRECTORY_SEPARATOR.'TestFiles'.\DIRECTORY_SEPARATOR.'testCSVUnknownDelimiter.csv';

    $this->expectException('RuntimeException');
    DataFrame::fromCSV($fileName);
});

test('invalid column count', function (): void {
    $fileName = __DIR__.\DIRECTORY_SEPARATOR.'TestFiles'.\DIRECTORY_SEPARATOR.'testCSVInvalidColumnCount.csv';

    $this->expectException('CondorcetPHP\Oliphant\Exceptions\InvalidColumnException');
    DataFrame::fromCSV($fileName);
});
