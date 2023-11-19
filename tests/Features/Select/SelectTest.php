<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;

beforeEach(function (): void {
    $this->df = DataFrame::fromArray([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
        ['colA' => 10, 'colB' => 11, 'colC' => 12],
        ['colA' => 13, 'colB' => 14, 'colC' => 15],
    ]);
});

it('can use a limit', function (): void {
    $newDf = $this->df->select('colA', 'colB', 'colC')->limit(2)->get();
    expect($newDf)->toHaveCount(2);
    expect($newDf->toArray())->toBe([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
    ]);

    $newDf = $this->df->select('colA', 'colB', 'colC')->limit(2, 1)->get();
    expect($newDf)->toHaveCount(2);
    expect($newDf->toArray())->toBe([
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});

it('iterable', function (): void {
    $select = $this->df->select('colC')->limit(4)->offset(2);

    $r = [];

    foreach ($select as $key => $value) {
        $r[$key] = $value;
    }

    expect($r)->toBe([
        2 => ['colC' => 9],
        3 => ['colC' => 12],
        4 => ['colC' => 15],
    ]);
});
