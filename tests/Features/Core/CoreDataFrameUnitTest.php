<?php

declare(strict_types=1);
use Archon\DataFrame;

beforeEach(function (): void {
    $this->input = $input = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $this->df = DataFrame::fromArray($this->input);
});

test('from array', function (): void {
    expect($this->df->toArray())->toEqual($this->input);
});

test('columns', function (): void {
    expect($this->df->columns())->toEqual(['a', 'b', 'c']);
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

test('offset get', function (): void {
    $a = $this->df['a'];
    $b = $this->df['b'];

    expect($a->toArray())->toEqual([['a' => 1], ['a' => 4], ['a' => 7]]);
    expect($b->toArray())->toEqual([['b' => 2], ['b' => 5], ['b' => 8]]);
});

test('offset set value', function (): void {
    $df = $this->df;
    $df['a'] = 321;

    $expected = [
        ['a' => 321, 'b' => 2, 'c' => 3],
        ['a' => 321, 'b' => 5, 'c' => 6],
        ['a' => 321, 'b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('offset set closure', function (): void {
    $df = $this->df;

    $add = static function ($x) {
        return static function ($y) use ($x) {
            return $x + $y;
        };
    };

    $df['a'] = $add(10);
    $df['b'] = $add(20);
    $df['c'] = $add(30);

    $expected = [
        ['a' => 11, 'b' => 22, 'c' => 33],
        ['a' => 14, 'b' => 25, 'c' => 36],
        ['a' => 17, 'b' => 28, 'c' => 39],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('offset set dataframe', function (): void {
    $df = $this->df;

    $df['a'] = $df['b'];

    $expected = [
        ['a' => 2, 'b' => 2, 'c' => 3],
        ['a' => 5, 'b' => 5, 'c' => 6],
        ['a' => 8, 'b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('offset set new column', function (): void {
    $df = $this->df;

    $df['d'] = $df['c']->apply(static function ($el) {
        return $el + 1;
    });

    $expected = [
        ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
        ['a' => 4, 'b' => 5, 'c' => 6, 'd' => 7],
        ['a' => 7, 'b' => 8, 'c' => 9, 'd' => 10],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('apply data frame', function (): void {
    $df = $this->df;

    $df->apply(static function ($row) {
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

test('isset', function (): void {
    expect(isset($this->df['a']))->toBeTrue();
    expect(isset($this->df['foo']))->toBeFalse();
});

test('apply index map values', function (): void {
    $df = $this->df;

    $df->applyIndexMap([
        0 => 0,
        2 => 0,
    ], 'a');

    expect($df->toArray())->toEqual([
        ['a' => 0, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 0, 'b' => 8, 'c' => 9],
    ]);
});

test('apply index map function', function (): void {
    $df = $this->df;

    $df->applyIndexMap([
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

test('apply index map value function', function (): void {
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

    $df->applyIndexMap([
        0 => $my_function,
        2 => $my_function,
    ], 'a');

    expect($df->toArray())->toEqual([
        ['a' => 0, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 1, 'b' => 8, 'c' => 9],
    ]);
});

test('apply index map array', function (): void {
    $df = $this->df;

    $df->applyIndexMap([
        1 => ['a' => 301, 'b' => 404, 'c' => 500],
    ]);

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 301, 'b' => 404, 'c' => 500],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('filter', function (): void {
    $df = $this->df;

    $df = $df->array_filter(static function ($row) {
        return $row['a'] > 4 || $row['a'] < 4;
    });

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
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

test('append', function (): void {
    $df1 = $this->df;
    $df2 = $this->df;

    // Test that appending an array with less than count of 1 will simply return the original DataFrame
    expect($df1->append(DataFrame::fromArray([])))->toBe($df1);

    $df1->append($df2);

    expect($df1->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('preg replace', function (): void {
    $df1 = $this->df;

    $df1->preg_replace('/[1-5]/', 'foo');

    expect($df1->toArray())->toEqual([
        ['a' => 'foo', 'b' => 'foo', 'c' => 'foo'],
        ['a' => 'foo', 'b' => 'foo', 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('group by', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
    ]);

    expect($df->unique('a')->toArray())->toBe([
        ['a' => 1],
        ['a' => 2],
        ['a' => 3],
    ]);

    expect($df->unique(['a', 'b'])->toArray())->toBe([
        ['a' => 1, 'b' => 2],
        ['a' => 1, 'b' => 3],
        ['a' => 2, 'b' => 4],
        ['a' => 3, 'b' => 5],
    ]);

    expect($df->unique(['a', 'b', 'c'])->toArray())->toBe([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
    ]);
});

test('rename', function (): void {
    $df = $this->df;

    $df->renameColumn('a', 'foo');

    expect($df->toArray())->toBe([
        ['foo' => 1, 'b' => 2, 'c' => 3],
        ['foo' => 4, 'b' => 5, 'c' => 6],
        ['foo' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('sort values', function (): void {
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

    $unordered_df->sortValues('a');

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

    $unordered_df->sortValues(by: 'a', ascending: false);

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

    $unordered_df->sortValues(['a', 'x']);

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

    $unordered_df->sortValues(['b', 'a']);

    expect($unordered_df->toArray())->toBe($ordered_df->toArray());
});
