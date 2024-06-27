<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;

test('tests some api concept', function (): void {
    $df = DataFrame::fromArray([
        ['colA' => 'foo', 'colB' => 10, 'colC' => 7],
        ['colA' => 'bar', 'colB' => 80, 'colC' => 7],
    ]);

    $df->insert()->record(['colA' => 'foo', 'colB' => 42, 'colC' => 7]);

    $r = $df->select('colB')->whereColumn('colA', 'foo')->sum(); // 52
    expect($r)->toBe(52);

    $exportedDf = $df->extract()->fromSqlQuery('SELECT colA, sum(colB) FROM dataframe GROUP BY 1;');

    expect($exportedDf->toArray())->toHaveCount(2);
});
