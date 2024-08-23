<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Stats\Helpers\AbstractAgg;
use MammothPHP\WoollyM\Stats\Modules;

class SumOdd extends AbstractAgg
{
    public const string NAME = 'sumOdd';

    public function addValue(mixed $value): void
    {
        if ($value === true) {
            $value = 1;
        }

        if (!empty($value) && is_numeric($value)) {
            if (\is_string($value) && ctype_digit($value)) {
                $value = \intval($value);
            } elseif (!\is_int($value)) {
                $value = \floatval($value);
            }

        if($value % 2 !== 0)
            $this->agg += $value;
        }
    }
}


test('register modules', function(): void {
    Modules::registerModule(SumOdd::class);

    // Use it!

    $df = new DataFrame([
        ['colA' => 42, 'colB' => 7 , 'colC' => 8],
        ['colA' => 77, 'colB' => 7 , 'colC' => 42],
        ['colA' => 77, 'colB' => 9 , 'colC' => 8],
        ['colA' => 42, 'colB' => 7 , 'colC' => 42],
        ['colA' => 77, 'colB' => 8 , 'colC' => 8],
    ]);

    expect($df->select('colB')->sumOdd())->toBe(7+7+9+7);
});