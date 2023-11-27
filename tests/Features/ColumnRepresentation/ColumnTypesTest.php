<?php

declare(strict_types=1);

use MammothPHP\WoollyM\{DataFrame, DataFrameModifiers, DataType};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

it('can apply type', function (): void {
    $this->df->col('b')->type(DataType::STRING);

    expect($this->df->col('b')->toArray())->toBe([['b' => '2'], ['b' => '5'], ['b' => '8']]);
});

it('can force type', function (): void {
    $this->df->col('b')->enforceType(DataType::STRING);

    expect($this->df->col('b')->toArray())->toBe([['b' => '2'], ['b' => '5'], ['b' => '8']]);

    $this->df[] = ['b' => 42, 'c' => 42];

    expect($this->df->col('b')->toArray())->toBe([['b' => '2'], ['b' => '5'], ['b' => '8'], ['b' => '42']]);

    expect($this->df->toArray())->toBe([
        ['a' => 1, 'b' => '2', 'c' => 3],
        ['a' => 4, 'b' => '5', 'c' => 6],
        ['a' => 7, 'b' => '8', 'c' => 9],
        ['b' => '42', 'c' => 42],
    ]);

    $this->df->col('b')->enforceType(null);

    $this->df[] = ['b' => 43, 'c' => 43];

    expect($this->df->col('b')->toArray())->toBe([['b' => '2'], ['b' => '5'], ['b' => '8'], ['b' => '42'], ['b' => 43]]);
});
