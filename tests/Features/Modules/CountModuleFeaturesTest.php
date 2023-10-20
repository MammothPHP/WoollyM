<?php

declare(strict_types=1);

use CondorcetPHP\Oliphant\{DataFrame, DataFrameCore};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => null, 'c' => 3],
        ['a' => 4, 'b' => '', 'c' => false],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});


test('count column B', function (): void {
    $expected = 1;

    expect($this->df->col('b')->count())->toBe($expected);
    expect($this->df->col('b')->count)->toBe($expected);
});

test('count column C', function (): void {
    $expected = 2;

    expect($this->df->col('c')->count())->toBe($expected);
    expect($this->df->col('c')->count)->toBe($expected);
});
