<?php

declare(strict_types=1);

use CondorcetPHP\Oliphant\{DataFrame, DataFrameCore};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => 'z', 'c' => 3],
        ['a' => 4, 'b' => ' 5', 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('sum column C', function (): void {
    $expected = 3 + 6 + 9;

    expect($this->df->col('c')->sum())->toBe($expected);
    expect($this->df->col('c')->sum)->toBe($expected);
});


test('sum column B', function (): void {
    $expected = 5 + 8;

    expect($this->df->col('b')->sum())->toBe($expected);
    expect($this->df->col('b')->sum)->toBe($expected);
});
