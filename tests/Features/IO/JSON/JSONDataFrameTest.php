<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;

test('to json', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $expected = '[{"a":1,"b":2,"c":3},{"a":4,"b":5,"c":6},{"a":7,"b":8,"c":9}]';
    expect($df->toJSON())->toEqual($expected);
});

test('from json', function (): void {
    $expected = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $filePath = __DIR__.DIRECTORY_SEPARATOR.'TestFiles'.DIRECTORY_SEPARATOR.'input.json';

    $df = DataFrame::fromJsonString(file_get_contents($filePath));
    expect($df->toArray())->toEqual($expected);

    $df = DataFrame::fromJsonFile($filePath);
    expect($df->toArray())->toEqual($expected);
});

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

    expect($df->toJSON(pretty: true))->toEqual($expected);
});
