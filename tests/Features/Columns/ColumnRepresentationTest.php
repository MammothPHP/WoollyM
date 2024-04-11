<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{DataFrameException, InvalidSelectException};

beforeEach(function (): void {
    $this->df = new DataFrame([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $this->expected1 = [
        ['a' => 1, 'b' => 42, 'c' => 3],
        ['a' => 4, 'b' => 42, 'c' => 6],
        ['a' => 7, 'b' => 42, 'c' => 9],
    ];
});

test('alias equivalence', fn() => expect($this->df->col('b'))->toBe($this->df->column('b')));

test('name and rename', function (): void {
    $expected = 'c';

    expect($this->df->col($expected)->name)->toBe($expected);

    expect($this->df->col($expected)->rename($expected = 'newName')->name)->toBe($expected);

    expect($this->df->col($expected)->limit(2)->sum())->toBe(9);
});


test('set column raw value', function (): void {
    $return = $this->df->col('b')->set(42);

    expect($this->df->toArray())->toBe($this->expected1);

    expect($return)->toBe($this->df->col('b'));
});

it('apply closure to column', function (): void {
    $this->df->col('b')->set(fn($v): int => 42);

    expect($this->df->toArray())->toBe($this->expected1);
});

it('apply dataframe to colone', function (): void {
    $this->df->col('b')->set(
        new DataFrame([['b' => 42], ['b' => 42], ['b' => 42]])
    );

    expect($this->df->toArray())->toBe($this->expected1);
});

it('cannot apply invalid dataframe with 2 columns', function (): void {
    $this->df->col('b')->set(
        new DataFrame([['b' => 42], ['c' => 42], ['b' => 42]])
    );
})->throws(DataFrameException::class);

it('cannot apply invalid dataframe with different number of records', function (): void {
    $this->df->col('b')->set(
        new DataFrame([['b' => 42], ['b' => 42]])
    );
})->throws(DataFrameException::class);

test('set column raw value from property', function (): void {
    $this->df->col('b')->values = 42;

    expect($this->df->toArray())->toBe($this->expected1);
});

it('remove himself', function (): void {
    $colB = $this->df->col('b');
    $return = $colB->remove();

    expect($this->df->columns())->toHaveCount(2);

    expect(fn() => $this->df->col('b'))->toThrow(InvalidSelectException::class);
    expect(fn() => $colB->sum())->toThrow(InvalidSelectException::class);
});

it('has dynamic properties', fn(string $prop) => expect(isset($this->df->col('c')->{$prop}))->toBeTrue())
    ->with(['average', 'sum']);
