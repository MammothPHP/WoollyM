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


test('update a record', function (): void {
    $newValue = ['colB' => 64];
    $expected = $this->df->toArray();
    $expected[3] = $newValue;

    $this->df->update()->record(3, $newValue);

    expect($this->df->toArray())->toBe($expected);
});

test('merge record', function (): void {
    $newValue = ['colB' => 64, 'colD' => 'foo'];
    $expected = $this->df->toArray();
    $expected[3] = array_merge($expected[3], $newValue);

    $this->df->update()->mergeRecord(3, $newValue);

    expect($this->df->toArray())->toBe($expected);
});
