<?php

declare(strict_types=1);

use MammothPHP\WoollyM\{DataFrame, DataFrameCore};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => 'z', 'c' => 3],
        ['a' => 4, 'b' => ' 5', 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('name module', function (): void {
    $expected = 'c';

    expect($this->df->col($expected)->name)->toBe($expected);

    expect($this->df->col($expected)->rename($expected = 'newName')->name)->toBe($expected);
});
