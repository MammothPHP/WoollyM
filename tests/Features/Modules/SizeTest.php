<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => null, 'c' => 3],
        ['a' => 4, 'b' => '', 'c' => false],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('size column B', function (): void {
    expect($this->df->col('b')->size())
        ->toBe($this->df->col('b')->size)
        ->toBe(3)
    ;
});

test('size all', function (): void {
    expect($this->df->selectAll()->size())
        ->toBe($this->df->selectAll()->size)
        ->toBe(3 * 3)
    ;
});

test('size with filters', function (): void {
    $df = new DataFrame([
        ['colA' => 42, 'colB' => 7, 'colC' => 8],
        ['colA' => 77, 'colB' => 7, 'colC' => 42],
        ['colA' => 77, 'colB' => 7, 'colC' => 8],
        ['colA' => 42, 'colB' => 7, 'colC' => 42],
        ['colA' => 77, 'colB' => 7, 'colC' => 8],
    ]);

    expect($df->select('colA', 'colC')->whereColumn('colA', 42)->size())->toBe(2 * 2);
});

test('count column B', function (): void {
    $expected = 2;

    expect($this->df->col('b')->size(ignoreNullValue: true))->toBe($expected);
});

test('count column C', function (): void {
    $expected = 3;

    expect($this->df->col('c')->size(ignoreNullValue: true))->toBe($expected)
    ;
});

test('count all values', function (): void {
    $expected = 8;

    expect($this->df->select('a', 'b', 'c')->size(ignoreNullValue: true))->toBe($expected)
    ;
});
