<?php

declare(strict_types=1);

use MammothPHP\WoollyM\{DataFrame, DataFrameCore};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => null, 'c' => 3],
        ['a' => 4, 'b' => '', 'c' => false],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('count column B', function (): void {
    $expected = 2;

    $r = $this->df->col('b')->count();
    expect($this->df->col('b')->count())
        ->toBe($this->df->col('b')->count)
        ->toBe($expected)
    ;
});

test('count column C', function (): void {
    $expected = 3;

    expect($this->df->col('c')->count())
        ->toBe($this->df->col('c')->count)
        ->toBe($expected)
    ;
});

test('count all values', function (): void {
    $expected = 8;

    expect($this->df->select('a', 'b', 'c')->count())
        ->toBe($this->df->select('a', 'b', 'c')->count)
        ->toBe($expected)
    ;
});
