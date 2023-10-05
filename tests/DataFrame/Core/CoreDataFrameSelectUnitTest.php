<?php

declare(strict_types=1);
use Archon\DataFrame;

test('data frame select', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $df = $df->query("SELECT a, c
        FROM dataframe
        WHERE a = '4'
          OR b = '2';");

    $expected = [
        ['a' => 1, 'c' => 3],
        ['a' => 4, 'c' => 6],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('data frame select update', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $df = $df->query('UPDATE dataframe
        SET a = c * 2;');

    $expected = [
        ['a' => 6, 'b' => 2, 'c' => 3],
        ['a' => 12, 'b' => 5, 'c' => 6],
        ['a' => 18, 'b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});
