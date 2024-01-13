<?php

declare(strict_types=1);

use MammothPHP\WoollyM\{DataFrame, DataFrameModifiers};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => null, 'c' => 3],
        ['a' => 4, 'b' => '', 'c' => false],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('size column B', function (): void {
    $expected = 3;

    expect($this->df->col('b')->size())
        ->toBe($this->df->col('b')->size)
        ->toBe($expected)
    ;
});

test('size all', function (): void {
    $expected = 9;

    expect($this->df->selectAll()->size())
        ->toBe($this->df->selectAll()->size)
        ->toBe($expected)
    ;
});

test('size with filters', function(): void {
    $df = new DataFrame([
        ['colA' => 42, 'colB' => 7 , 'colC' => 8],
        ['colA' => 77, 'colB' => 7 , 'colC' => 42],
        ['colA' => 77, 'colB' => 7 , 'colC' => 8],
        ['colA' => 42, 'colB' => 7 , 'colC' => 42],
        ['colA' => 77, 'colB' => 7 , 'colC' => 8],
    ]);

    expect($df->select('colA', 'colC')->whereColumnEqual('colA', 42)->size())->toBe(4);
});
