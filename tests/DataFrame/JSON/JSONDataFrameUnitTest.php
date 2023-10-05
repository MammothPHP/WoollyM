<?php

declare(strict_types=1);
use Archon\DataFrame;

test('to j s o n', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $expected = '[{"a":1,"b":2,"c":3},{"a":4,"b":5,"c":6},{"a":7,"b":8,"c":9}]';
    expect($df->toJSON())->toEqual($expected);
});

test('from j s o n', function (): void {
    $df = DataFrame::fromJSON('[{"a":1,"b":2,"c":3},{"a":4,"b":5,"c":6},{"a":7,"b":8,"c":9}]');

    $expected = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('to pretty j s o n', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $expected = '['."\n";
    $expected .= '    {'."\n";
    $expected .= '        "a": 1,'."\n";
    $expected .= '        "b": 2,'."\n";
    $expected .= '        "c": 3'."\n";
    $expected .= '    },'."\n";
    $expected .= '    {'."\n";
    $expected .= '        "a": 4,'."\n";
    $expected .= '        "b": 5,'."\n";
    $expected .= '        "c": 6'."\n";
    $expected .= '    },'."\n";
    $expected .= '    {'."\n";
    $expected .= '        "a": 7,'."\n";
    $expected .= '        "b": 8,'."\n";
    $expected .= '        "c": 9'."\n";
    $expected .= '    }'."\n";
    $expected .= ']';
    expect($df->toJSON(['pretty' => true]))->toEqual($expected);
});
