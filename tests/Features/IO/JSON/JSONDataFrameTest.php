<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\JSON;

test('to json', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $expected = '[{"a":1,"b":2,"c":3},{"a":4,"b":5,"c":6},{"a":7,"b":8,"c":9}]';
    expect(JSON::fromDataFrame($df)->toString())->toEqual($expected);

    $tempFile = new SplTempFileObject;
    JSON::fromDataFrame($df)->toFile(file: $tempFile);

    $tempFile->rewind();
    expect($tempFile->fread(1024))->toBe($expected);
});

test('from json', function (string $type, string $input): void {
    $expected = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $df = ($type === 'string') ? JSON::fromString($input)->import() : JSON::fromFilePath($input)->import();


    expect($df->toArray())->toEqual($expected);
})->with([
    ['string', file_get_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'input.json')],
    ['file path', __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'input.json'],
]);

test('to pretty json', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $expected = '[' . "\n";
    $expected .= '    {' . "\n";
    $expected .= '        "a": 1,' . "\n";
    $expected .= '        "b": 2,' . "\n";
    $expected .= '        "c": 3' . "\n";
    $expected .= '    },' . "\n";
    $expected .= '    {' . "\n";
    $expected .= '        "a": 4,' . "\n";
    $expected .= '        "b": 5,' . "\n";
    $expected .= '        "c": 6' . "\n";
    $expected .= '    },' . "\n";
    $expected .= '    {' . "\n";
    $expected .= '        "a": 7,' . "\n";
    $expected .= '        "b": 8,' . "\n";
    $expected .= '        "c": 9' . "\n";
    $expected .= '    }' . "\n";
    $expected .= ']';

    expect(JSON::fromDataFrame($df)->toString(pretty: true))->toEqual($expected);
});
