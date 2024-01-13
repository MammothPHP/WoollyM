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


test('delete record', function (): void {
    $expected = $this->df->toArray();
    unset($expected[1]);

    $this->df->delete()->record(1);

    expect($this->df->toArray())->toBe($expected);
});

test('delete all statement', function(): void {
    $this->df->delete()
        ->whereColumnEqual('colB', 5)
        ->or(fn (array $record): bool => $record['colA'] >= 10)
        ->execute();

    expect($this->df->toArray())->toBe([
        0 => ['colA' => 1, 'colB' => 2, 'colC' => 3],
        2 => ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});
