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

test('set new column', function (): void {
    expect($this->df->hasColumn('d'))->toBeFalse();

    $this->df
        ->setColumn('d', $this->df->col('c')->get()->apply(static function ($el) {
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

test('has column', function (): void {
    expect($this->df->hasColumn('a'))->toBeTrue();
    expect($this->df->hasColumn('foo'))->toBeFalse();
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

test('array filter', function (): void {
    $df = $this->df;

    $df = $df->array_filter(static function ($row) {
        return $row['a'] > 4 || $row['a'] < 4;
    });

    expect($df->toArray())->toEqual([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('filter', function (): void {
    $this->df->filter(function (array $rowArray, int $position): bool {
        if ($position === 1 || \in_array(7, $rowArray, true)) {
            return false;
        }

        return true;
    });

    expect($this->df->toArray())->toBe([
        0 => ['a' => 1, 'b' => 2, 'c' => 3],
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
    $df2 = clone $this->df;

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

    $df->col('a')->rename('foo');

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
