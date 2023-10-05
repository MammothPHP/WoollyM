<?php

declare(strict_types=1);

namespace Archon\Tests\DataFrame\Core;

use Archon\DataFrame;
use PHPUnit\Framework\TestCase;

class CoreDataFrameUnitTest extends TestCase
{
    /** @var DataFrame */
    private $df;

    private $input = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    protected function setUp(): void
    {
        $this->df = DataFrame::fromArray($this->input);
    }

    public function testFromArray(): void
    {
        $this->assertEquals($this->input, $this->df->toArray());
    }

    public function testColumns(): void
    {
        $this->assertEquals(['a', 'b', 'c'], $this->df->columns());
    }

    public function testRemoveColumn(): void
    {
        $df = $this->df;

        $df->removeColumn('a');
        $expected = [
            ['b' => 2, 'c' => 3],
            ['b' => 5, 'c' => 6],
            ['b' => 8, 'c' => 9],
        ];

        $this->assertEquals($expected, $df->toArray());
    }

    public function testForEach(): void
    {
        foreach ($this->df as $i => $row) {
            $this->assertEquals($row, $this->input[$i]);
        }
    }

    public function testOffsetGet(): void
    {
        $a = $this->df['a'];
        $b = $this->df['b'];

        $this->assertEquals([['a' => 1], ['a' => 4], ['a' => 7]], $a->toArray());
        $this->assertEquals([['b' => 2], ['b' => 5], ['b' => 8]], $b->toArray());
    }

    public function testOffsetSetValue(): void
    {
        $df = $this->df;
        $df['a'] = 321;

        $expected = [
            ['a' => 321, 'b' => 2, 'c' => 3],
            ['a' => 321, 'b' => 5, 'c' => 6],
            ['a' => 321, 'b' => 8, 'c' => 9],
        ];

        $this->assertEquals($expected, $df->toArray());
    }

    public function testOffsetSetClosure(): void
    {
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

        $this->assertEquals($expected, $df->toArray());
    }

    public function testOffsetSetDataframe(): void
    {
        $df = $this->df;

        $df['a'] = $df['b'];

        $expected = [
            ['a' => 2, 'b' => 2, 'c' => 3],
            ['a' => 5, 'b' => 5, 'c' => 6],
            ['a' => 8, 'b' => 8, 'c' => 9],
        ];

        $this->assertEquals($expected, $df->toArray());
    }

    public function testOffsetSetNewColumn(): void
    {
        $df = $this->df;

        $df['d'] = $df['c']->apply(static function ($el) {
            return $el + 1;
        });

        $expected = [
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            ['a' => 4, 'b' => 5, 'c' => 6, 'd' => 7],
            ['a' => 7, 'b' => 8, 'c' => 9, 'd' => 10],
        ];

        $this->assertEquals($expected, $df->toArray());
    }

    public function testApplyDataFrame(): void
    {
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

        $this->assertEquals($expected, $df->toArray());
    }

    public function testIsset(): void
    {
        $this->assertTrue(isset($this->df['a']));
        $this->assertFalse(isset($this->df['foo']));
    }

    public function testApplyIndexMapValues(): void
    {
        $df = $this->df;

        $df->applyIndexMap([
            0 => 0,
            2 => 0,
        ], 'a');

        $this->assertEquals([
            ['a' => 0, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 0, 'b' => 8, 'c' => 9],
        ], $df->toArray());
    }

    public function testApplyIndexMapFunction(): void
    {
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

        $this->assertEquals([
            ['a' => 10, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 20],
        ], $df->toArray());
    }

    public function testApplyIndexMapValueFunction(): void
    {
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

        $this->assertEquals([
            ['a' => 0, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 1, 'b' => 8, 'c' => 9],
        ], $df->toArray());
    }

    public function testApplyIndexMapArray(): void
    {
        $df = $this->df;

        $df->applyIndexMap([
            1 => ['a' => 301, 'b' => 404, 'c' => 500],
        ]);

        $this->assertEquals([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 301, 'b' => 404, 'c' => 500],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ], $df->toArray());
    }

    public function testFilter(): void
    {
        $df = $this->df;

        $df = $df->array_filter(static function ($row) {
            return $row['a'] > 4 || $row['a'] < 4;
        });

        $this->assertEquals([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ], $df->toArray());
    }

    public function testOffsetSetValueArray(): void
    {
        $df = $this->df;

        $df[] = ['a' => 10, 'b' => 11, 'c' => 12];

        $this->assertEquals([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
            ['a' => 10, 'b' => 11, 'c' => 12],
        ], $df->toArray());
    }

    public function testAppend(): void
    {
        $df1 = $this->df;
        $df2 = $this->df;

        // Test that appending an array with less than count of 1 will simply return the original DataFrame
        $this->assertSame(
            $df1,
            $df1->append(DataFrame::fromArray([]))
        );

        $df1->append($df2);

        $this->assertEquals([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ], $df1->toArray());
    }

    public function testPregReplace(): void
    {
        $df1 = $this->df;

        $df1->preg_replace('/[1-5]/', 'foo');

        $this->assertEquals([
            ['a' => 'foo', 'b' => 'foo', 'c' => 'foo'],
            ['a' => 'foo', 'b' => 'foo', 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ], $df1->toArray());
    }

    public function testGroupBy(): void
    {
        $df = DataFrame::fromArray([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 3, 'c' => 4],
            ['a' => 2, 'b' => 4, 'c' => 5],
            ['a' => 2, 'b' => 4, 'c' => 6],
            ['a' => 3, 'b' => 5, 'c' => 7],
            ['a' => 3, 'b' => 5, 'c' => 8],
        ]);

        $this->assertSame([
            ['a' => 1],
            ['a' => 2],
            ['a' => 3],
        ], $df->unique('a')->toArray());

        $this->assertSame([
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 3],
            ['a' => 2, 'b' => 4],
            ['a' => 3, 'b' => 5],
        ], $df->unique(['a', 'b'])->toArray());

        $this->assertSame([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 3, 'c' => 4],
            ['a' => 2, 'b' => 4, 'c' => 5],
            ['a' => 2, 'b' => 4, 'c' => 6],
            ['a' => 3, 'b' => 5, 'c' => 7],
            ['a' => 3, 'b' => 5, 'c' => 8],
        ], $df->unique(['a', 'b', 'c'])->toArray());
    }

    public function testRename(): void
    {
        $df = $this->df;

        $df->renameColumn('a', 'foo');

        $this->assertSame([
            ['foo' => 1, 'b' => 2, 'c' => 3],
            ['foo' => 4, 'b' => 5, 'c' => 6],
            ['foo' => 7, 'b' => 8, 'c' => 9],
        ], $df->toArray());
    }



    public function testSortValues(): void
    {

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

        $this->assertSame($unordered_df->toArray(), $ordered_df->toArray());


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

        $this->assertSame($ordered_df->toArray(), $unordered_df->toArray());

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

        $this->assertSame($ordered_df->toArray(), $unordered_df->toArray());


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

        $this->assertSame($ordered_df->toArray(), $unordered_df->toArray());
    }

}
