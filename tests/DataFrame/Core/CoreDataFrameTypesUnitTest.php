<?php

declare(strict_types=1);

namespace Archon\Tests\DataFrame\Core;

use Archon\{DataFrame, DataType};
use PHPUnit\Framework\TestCase;

class CoreDataFrameTypesUnitTest extends TestCase
{
    public function testConvertNumericInteger(): void
    {
        $df = DataFrame::fromArray([
            ['numeric' => 1,              'integer' => 3.14],
            ['numeric' => -4,             'integer' => '$5.23'],
            ['numeric' => 7.23,           'integer' => '8x'],
            ['numeric' => .3,             'integer' => '8-'],
            ['numeric' => '$3,000,000.4', 'integer' => 'asdf'],
            ['numeric' => '3.0-',         'integer' => '$3,456,789.23'],
        ]);

        $df->convertTypes([
            'numeric' => DataType::NUMERIC,
            'integer' => DataType::INTEGER,
        ]);

        foreach ($df as $row) {
            $this->assertIsNumeric($row['numeric']);
            $this->assertIsInt($row['integer']);
        }

        $this->assertSame([
            ['numeric' => 1,            'integer' => 3],
            ['numeric' => -4,           'integer' => 5],
            ['numeric' => 7.23,         'integer' => 8],
            ['numeric' => 0.3,          'integer' => -8],
            ['numeric' => 3000000.4,    'integer' => 0],
            ['numeric' => -3.0,         'integer' => 3456789],
        ], $df->toArray());
    }

    public function testConvertDateTime(): void
    {
        $df = DataFrame::fromArray([
            ['datetime' => '12/03/1996'],
            ['datetime' => '03-2001-04'],
            ['datetime' => 'Jun 04 2010'],
            ['datetime' => ''],
        ]);

        $df->convertTypes([
            'datetime' => DataType::DATETIME,
        ], ['d/m/Y', 'd-Y-m', 'M d Y'], 'Y-m-d');

        $this->assertSame([
            ['datetime' => '1996-03-12'],
            ['datetime' => '2001-04-03'],
            ['datetime' => '2010-06-04'],
            ['datetime' => '0001-01-01'],
        ], $df->toArray());

        $df->convertTypes([
            'datetime' => DataType::DATETIME,
        ], 'Y-m-d', 'M d Y');

        $this->assertSame([
            ['datetime' => 'Mar 12 1996'],
            ['datetime' => 'Apr 03 2001'],
            ['datetime' => 'Jun 04 2010'],
            ['datetime' => 'Jan 01 0001'],
        ], $df->toArray());

        $this->expectExceptionMessage("Error parsing date string 'Mar 12 1996' with date format Y-m-d");
        $df->convertTypes([
            'datetime' => DataType::DATETIME,
        ], 'Y-m-d', 'Y-m-d');

    }

    public function testConvertCurrencyACCOUNTING(): void
    {
        $df = DataFrame::fromArray([
            ['currency' => '1',          'accounting' => '1'],
            ['currency' => '-123456789', 'accounting' => '-123456789'],
            ['currency' => '',           'accounting' => ''],
            ['currency' => '123.45',     'accounting' => '123.45'],
            ['currency' => 'asdf',       'accounting' => 'asdf'],
            ['currency' => 'asdf.56-',   'accounting' => 'asdf.56-'],
        ]);

        $df->convertTypes([
            'currency'   => DataType::CURRENCY,
            'accounting' => DataType::ACCOUNTING,
        ]);

        $this->assertSame([
            ['currency' => '$1.00',            'accounting' => '$1.00'],
            ['currency' => '-$123,456,789.00', 'accounting' => '$(123,456,789.00)'],
            ['currency' => '$0.00',            'accounting' => '$0.00'],
            ['currency' => '$123.45',          'accounting' => '$123.45'],
            ['currency' => '$0.00',            'accounting' => '$0.00'],
            ['currency' => '-$0.56',           'accounting' => '$(0.56)'],
        ], $df->toArray());

    }

}
