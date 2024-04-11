<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;

beforeEach(function (): void {
    $this->describeKeys = ['count records', 'size', 'sum', 'mean', 'max', 'min'];

    $this->df = new DataFrame([
        ['a' => 1, 'b' => null, 'c' => 3],
        ['a' => 4, 'b' => '', 'c' => false],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('describe column', function (): void {
    expect($this->df->col('b')->describe())
        ->toBeArray()
        ->toHaveKeys($this->describeKeys)
    ;
});

test('describe select', function (): void {
    expect($this->df->selectAll()->describe())
        ->toBeArray()
        ->toHaveKeys($this->describeKeys)
    ;
});
