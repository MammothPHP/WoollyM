<?php

declare(strict_types=1);

use CondorcetPHP\Oliphant\{DataFrame, DataFrameCore};
use CondorcetPHP\Oliphant\Exceptions\InvalidColumnException;

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

test('alias equivalence', fn () => expect($this->df->col('b'))->toBe($this->df->column('b')));

test('set column raw value', function (): void {
    $return = $this->df->col('b')->setValues(42);

    expect($this->df->toArray())->toBe($this->expected1);

    expect($return)->toBe($this->df->col('b'));
});

it('apply closure to column', function (): void {
    $this->df->col('b')->setValues(fn ($v): int => 42);

    expect($this->df->toArray())->toBe($this->expected1);
});

it('apply dataframe to colone', function (): void {
    $this->df->col('b')->setValues(
        new DataFrame([['b' => 42], ['b' => 42], ['b' => 42]])
    );

    expect($this->df->toArray())->toBe($this->expected1);
});

test('set column raw value from property', function (): void {
    $this->df->col('b')->values = 42;

    expect($this->df->toArray())->toBe($this->expected1);
});

it('remove him self', function (): void {
    $colB = $this->df->col('b');
    $return = $colB->remove();

    expect($return)->toBeInstanceOf(DataFrameCore::class);
    expect($return->columns())->toHaveCount(2);

    expect(fn () => $return->col('b'))->toThrow(InvalidColumnException::class);
    expect(fn () => $colB->sum())->toThrow(InvalidColumnException::class);
});

it('has dynamic properties', fn (string $prop) => expect(isset($this->df->col('c')->{$prop}))->toBeTrue())
    ->with(['average', 'count', 'sum']);
