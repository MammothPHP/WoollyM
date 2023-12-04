<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;

beforeEach(function (): void {
    $this->input = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $this->df = DataFrame::fromArray($this->input);
});

test('to array', function (): void {
    expect($this->df->toArray())->toEqual($this->input);
});

test('isset and unset', function (): void {
    expect(isset($this->df[0]))->toBeTrue();
    expect(isset($this->df[1]))->toBeTrue();
    expect(isset($this->df[2]))->toBeTrue();
    expect(isset($this->df[42]))->toBeFalse();

    unset($this->df[1]);

    expect(isset($this->df[0]))->toBeTrue();
    expect(isset($this->df[1]))->toBeFalse();
    expect(isset($this->df[2]))->toBeTrue();
});

test('offset set value array', function (): void {
    $df = $this->df;

    $df[] = ['a' => 10, 'b' => 11, 'c' => 12];

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
        ['a' => 10, 'b' => 11, 'c' => 12],
    ]);
});

test('offset get', function (): void {
    expect($this->df[2])->toBe(['a' => 7, 'b' => 8, 'c' => 9]);
});
