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

test('column get', function (): void {
    $df = $this->df->col('a')->get(); // call as method

    expect($df->toArray())->toEqual([['a' => 1], ['a' => 4], ['a' => 7]]);
});

test('column set value', function (): void {
    $df = $this->df;
    $df->col('a')->set(321);

    $expected = [
        ['a' => 321, 'b' => 2, 'c' => 3],
        ['a' => 321, 'b' => 5, 'c' => 6],
        ['a' => 321, 'b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('column set closure', function (): void {
    $df = $this->df;

    $add = static function ($x) {
        return static function ($y) use ($x) {
            return $x + $y;
        };
    };

    $df->col('a')->set($add(10));
    $df->col('b')->set($add(20));
    $df->col('c')->set($add(30));

    $expected = [
        ['a' => 11, 'b' => 22, 'c' => 33],
        ['a' => 14, 'b' => 25, 'c' => 36],
        ['a' => 17, 'b' => 28, 'c' => 39],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('column set dataframe', function (): void {
    $df = $this->df;

    $df->col('a')->set($df->col('b')->get());

    $expected = [
        ['a' => 2, 'b' => 2, 'c' => 3],
        ['a' => 5, 'b' => 5, 'c' => 6],
        ['a' => 8, 'b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('rename', function (): void {
    $df = $this->df;

    $df->col('a')->rename('foo');

    expect($df->toArray())->toBe([
        ['foo' => 1, 'b' => 2, 'c' => 3],
        ['foo' => 4, 'b' => 5, 'c' => 6],
        ['foo' => 7, 'b' => 8, 'c' => 9],
    ]);
});
