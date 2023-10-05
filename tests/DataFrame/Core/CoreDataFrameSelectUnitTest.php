<?php

declare(strict_types=1);

namespace Archon\Tests\DataFrame\Core;

use Archon\DataFrame;
use PHPUnit\Framework\TestCase;

class CoreDataFrameSelectUnitTest extends TestCase
{
    public function testDataFrameSelect(): void
    {
        $df = DataFrame::fromArray([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ]);

        $df = $df->query("SELECT a, c
        FROM dataframe
        WHERE a = '4'
          OR b = '2';");

        $expected = [
            ['a' => 1, 'c' => 3],
            ['a' => 4, 'c' => 6],
        ];

        $this->assertEquals($expected, $df->toArray());
    }

    public function testDataFrameSelectUpdate(): void
    {
        $df = DataFrame::fromArray([
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 4, 'b' => 5, 'c' => 6],
            ['a' => 7, 'b' => 8, 'c' => 9],
        ]);

        $df = $df->query('UPDATE dataframe
        SET a = c * 2;');

        $expected = [
            ['a' => 6, 'b' => 2, 'c' => 3],
            ['a' => 12, 'b' => 5, 'c' => 6],
            ['a' => 18, 'b' => 8, 'c' => 9],
        ];

        $this->assertEquals($expected, $df->toArray());
    }


}
