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

test('internal references with chained methods', function (): void {
    // Normal way
    $newData = $this->df->col('a')->export();
    expect($newData->update()->isAlive())->toBeTrue();

    // Exported DataFrame must not die immediatly and modify action must not fail
    expect($this->df->col('a')->export()->update()->isAlive())->toBeTrue();
});

test('sort columns', function (): void {
    $df = new DataFrame([
        ['c' => 3, 'b' => 2, 'a' => 1],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    expect($df->columnsNames())->toBe([0 => 'c', 1 => 'b', 2 => 'a']);
    expect($df->toArray())->toBe([
        ['c' => 3, 'b' => 2, 'a' => 1],
        ['c' => 6, 'b' => 5, 'a' => 4],
        ['c' => 9, 'b' => 8, 'a' => 7],
    ]);

    $df->sortColumns();
    expect($df->columnsNames())->toBe([2 => 'a', 1 => 'b', 0 => 'c']);
    expect($df->toArray())->toBe([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('apply data frame', function (): void {
    $df = $this->df;

    $df->update()->apply(static function ($row) {
        $row['b'] = $row['a'] + 2;
        $row['c'] = $row['b'] + 2;

        return $row;
    });

    $expected = [
        ['a' => 1, 'b' => 3, 'c' => 5],
        ['a' => 4, 'b' => 6, 'c' => 8],
        ['a' => 7, 'b' => 9, 'c' => 11],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('apply index map function 1', function (): void {
    $df = $this->df;

    $df->update()->applyIndexMap(map: [
        0 => static function ($row) {
            $row['a'] = 10;

            return $row;
        },
        2 => static function ($row) {
            $row['c'] = 20;

            return $row;
        },
    ]);

    expect($df->toArray())->toEqual([
        ['a' => 10, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 20],
    ]);
});

test('apply index map function 2', function (): void {
    $df = $this->df;

    $df->update()->applyIndexMap(
        map: [
            0 => 'foo',
            1 => fn($oldValue) => $oldValue * 2,
            2 => 'baz',
        ],
        column: 'a'
    );

    expect($df->toArray())->toEqual([
        ['a' => 'foo', 'b' => 2, 'c' => 3],
        ['a' => 8, 'b' => 5, 'c' => 6],
        ['a' => 'baz', 'b' => 8, 'c' => 9],
    ]);
});

test('apply index map value function 1', function (): void {
    $df = $this->df;

    $my_function = static function ($value) {
        if ($value < 4) {
            return 0;
        } elseif ($value > 4) {
            return 1;
        } else {
            return $value;
        }
    };

    $df->update()->applyIndexMap(
        map: [
            0 => $my_function,
            2 => $my_function,
        ],
        column: 'a'
    );

    expect($df->toArray())->toEqual([
        ['a' => 0, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 1, 'b' => 8, 'c' => 9],
    ]);
});

test('apply index map value function 2', function (): void {
    $df = $this->df;

    $df->update()->applyIndexMap(
        map: [
            1 => fn(array $oldRecord): array => array_map(fn(int $v): int => $v * 2, $oldRecord),
            2 => ['a' => 1, 'b' => 2, 'c' => 3],
        ]
    );

    expect($df->toArray())->toEqual([
        0 => ['a' => 1, 'b' => 2, 'c' => 3],
        1 => ['a' => 8, 'b' => 10, 'c' => 12],
        2 => ['a' => 1, 'b' => 2, 'c' => 3],
    ]);
});

test('apply index map array', function (): void {
    $df = $this->df;

    $df->update()->applyIndexMap(map: [
        1 => ['a' => 301, 'b' => 404, 'c' => 500],
    ]);

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 301, 'b' => 404, 'c' => 500],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('filter', function (): void {
    $this->df->delete()->filter(function (array $rowArray, int $position): bool {
        if ($position === 1 || \in_array(7, $rowArray, true)) {
            return true;
        }

        return false;
    });

    expect($this->df->toArray())->toBe([
        0 => ['a' => 1, 'b' => 2, 'c' => 3],
    ]);
});

test('preg replace', function (): void {
    $df1 = $this->df;

    $df1->update()->preg_replace('/[1-5]/', 'foo');

    expect($df1->toArray())->toBe([
        ['a' => 'foo', 'b' => 'foo', 'c' => 'foo'],
        ['a' => 'foo', 'b' => 'foo', 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});



test('sort record by columns', function (): void {
    // Single column
    $unordered_df = DataFrame::fromArray([
        ['a' => 1, 'x' => 'a'],
        ['a' => 3, 'x' => 'b'],
        ['a' => 2, 'x' => 'c'],
        ['a' => 4, 'x' => 'd'],
    ]);

    $ordered_df = DataFrame::fromArray([
        ['a' => 1, 'x' => 'a'],
        ['a' => 2, 'x' => 'c'],
        ['a' => 3, 'x' => 'b'],
        ['a' => 4, 'x' => 'd'],
    ]);

    $unordered_df->sortRecordsByColumns('a');

    expect($ordered_df->toArray())->toBe($unordered_df->toArray());

    // Single column descending
    $unordered_df = DataFrame::fromArray([
        ['a' => 1, 'x' => 'a'],
        ['a' => 3, 'x' => 'b'],
        ['a' => 2, 'x' => 'c'],
        ['a' => 4, 'x' => 'd'],
    ]);

    $ordered_df = DataFrame::fromArray([
        ['a' => 4, 'x' => 'd'],
        ['a' => 3, 'x' => 'b'],
        ['a' => 2, 'x' => 'c'],
        ['a' => 1, 'x' => 'a'],
    ]);

    $unordered_df->sortRecordsByColumns(by: 'a', ascending: false);

    expect($unordered_df->toArray())->toBe($ordered_df->toArray());

    // Double column, first a than x
    $unordered_df = DataFrame::fromArray([
        ['a' => 1, 'b' => 5, 'x' => 'a'],
        ['a' => 2, 'b' => 3, 'x' => 'd'],
        ['a' => 2, 'b' => 2, 'x' => 'c'],
        ['a' => 4, 'b' => 1, 'x' => 'b'],
    ]);

    $ordered_df = DataFrame::fromArray([
        ['a' => 1, 'b' => 5, 'x' => 'a'],
        ['a' => 2, 'b' => 2, 'x' => 'c'],
        ['a' => 2, 'b' => 3, 'x' => 'd'],
        ['a' => 4, 'b' => 1, 'x' => 'b'],
    ]);

    $unordered_df->sortRecordsByColumns(['a', 'x']);

    expect($unordered_df->toArray())->toBe($ordered_df->toArray());

    // Double column, first b than a
    $unordered_df = DataFrame::fromArray([
        ['a' => 1, 'b' => 5, 'x' => 'a'],
        ['a' => 2, 'b' => 3, 'x' => 'b'],
        ['a' => 2, 'b' => 2, 'x' => 'c'],
        ['a' => 4, 'b' => 5, 'x' => 'd'],
    ]);

    $ordered_df = DataFrame::fromArray([
        ['a' => 2, 'b' => 2, 'x' => 'c'],
        ['a' => 2, 'b' => 3, 'x' => 'b'],
        ['a' => 1, 'b' => 5, 'x' => 'a'],
        ['a' => 4, 'b' => 5, 'x' => 'd'],
    ]);

    $unordered_df->sortRecordsByColumns(['b', 'a']);

    expect($unordered_df->toArray())->toBe($ordered_df->toArray());
});

test('apply index map values', function (): void {
    $df = $this->df;

    $df->update()->applyIndexMap([
        0 => 0,
        2 => 0,
    ], 'a');

    expect($df->toArray())->toEqual([
        ['a' => 0, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 0, 'b' => 8, 'c' => 9],
    ]);
});
