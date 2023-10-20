<?php

declare(strict_types=1);

use CondorcetPHP\Oliphant\{DataFrame, DataFrameCore};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => null, 'c' => 3],
        ['a' => '4 ', 'b' => '', 'c' => false],
        ['a' => 7, 'b' => 8, 'c' => 9],
        ['a' => 1, 'b' => 2, 'c' => true],
    ]);
});


test('average column B', function (): void {
    $expected = (8 + 2) / 2;

    expect($this->df->col('b')->average())->toBe($expected);
    expect($this->df->col('b')->average)->toBe($expected);
});

test('average column C', function (): void {
    $expected = (3 + 9 + 1) / 3;

    expect($this->df->col('c')->average())->toBe($expected);
    expect($this->df->col('c')->average)->toBe($expected);
});

test('average column A', function (): void {
    $expected = (1 + 4 + 7 + 1) / 4;

    expect($this->df->col('a')->average())->toBe($expected);
    expect($this->df->col('a')->average)->toBe($expected);
});
