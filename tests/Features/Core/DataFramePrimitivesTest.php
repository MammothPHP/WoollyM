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

test('head', function (): void {
    expect($this->df->head())->toBe($this->input);
    expect($this->df->head(2))->toBe(\array_slice($this->input, 0, 2, true));
    expect($this->df->head(columns: 'b'))->toBe([['b' => 2], ['b' => 5], ['b' => 8]]);
    expect($this->df->head(length: 1, offset: 1, columns: 'b'))->toBe([1 => ['b' => 5]]);
    expect($this->df->head(columns: ['b', 'c']))->toBe([
        ['b' => 2, 'c' => 3],
        ['b' => 5, 'c' => 6],
        ['b' => 8, 'c' => 9],
    ]);
});

test('columns', function (): void {
    expect($this->df->columns()[0])->toBe($this->df->col('a'));
    expect($this->df->columns())->toEqual(['a', 'b', 'c']);
});

test('columnsNames', function (): void {
    expect($this->df->columnsNames())->toBe(['a', 'b', 'c']);
});


test('remove column', function (): void {
    $df = $this->df;

    $df->removeColumn('a');
    $expected = [
        ['b' => 2, 'c' => 3],
        ['b' => 5, 'c' => 6],
        ['b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('for each', function (): void {
    foreach ($this->df as $i => $row) {
        expect($this->input[$i])->toEqual($row);
    }
});

test('addColumn', function (): void {
    $this->df->addColumn('foo');
    $this->df->addColumns(['bar', 'Z']);

    expect($this->df->col('foo')->getName('foo'))->toBe('foo');
    expect($this->df->col('bar')->getName('bar'))->toBe('bar');
    expect($this->df->col('Z')->getName('Z'))->toBe('Z');
});


test('set new column', function (): void {
    expect($this->df->hasColumn('d'))->toBeFalse();

    $newColumnData = $this->df->col('c')->export();

    $this->df->insert()
        ->setColumn('d', $newColumnData->update()->apply(static function ($el) {
            return $el + 1;
        }));

    expect($this->df->hasColumn('d'))->toBeTrue();

    $expected = [
        ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
        ['a' => 4, 'b' => 5, 'c' => 6, 'd' => 7],
        ['a' => 7, 'b' => 8, 'c' => 9, 'd' => 10],
    ];

    expect($this->df->toArray())->toEqual($expected);
});


test('has column', function (): void {
    expect($this->df->hasColumn('a'))->toBeTrue();
    expect($this->df->hasColumn('foo'))->toBeFalse();
});



test('append', function (): void {
    $df1 = $this->df;
    $df2 = clone $this->df;

    // Test that appending an array with less than count of 1 will simply return the original DataFrame
    expect($df1->insert()->append(DataFrame::fromArray([])))->toBe($df1);

    $df1->insert()->append($df2);

    expect($df1->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});
