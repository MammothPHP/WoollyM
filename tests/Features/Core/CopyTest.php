<?php

declare(strict_types=1);

use MammothPHP\WoollyM\{Copy, DataFrame};

beforeEach(function (): void {
    $this->input = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $this->df = DataFrame::fromArray($this->input);
});

test('clone', function (): void {
    $clone = $this->df->copy()->clone();

    expect($clone)->not->toBe($this->df);
    expect($clone->toArray())->toBe($this->df->toArray());
});

test('filter', function (): void {
    $df = $this->df;

    $df = $df->copy()->filter(static function ($row) {
        return $row['a'] > 4 || $row['a'] < 4;
    });

    expect($df)->not->toBe($this->df);

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('unique', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
    ]);

    expect($df->copy()->unique('a')->toArray())->toBe([
        ['a' => 1],
        ['a' => 2],
        ['a' => 3],
    ]);

    expect($df->copy()->unique(['a', 'b'])->toArray())->toBe([
        ['a' => 1, 'b' => 2],
        ['a' => 1, 'b' => 3],
        ['a' => 2, 'b' => 4],
        ['a' => 3, 'b' => 5],
    ]);

    $df = $df->copy()->unique(['a', 'b', 'c']);

    expect($df)->not->toBe($this->df);

    expect($df->toArray())->toBe([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
    ]);
});
