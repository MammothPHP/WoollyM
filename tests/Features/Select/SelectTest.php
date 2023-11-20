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

it('can be use a single condition', function (): void {
    $select = $this->df->select('colA', 'colB', 'colC')->where(fn(array $r): bool => $r['colB'] % 2 === 0); // Even numbers in colB only

    expect($select->get()->toArray())->toBe([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
        ['colA' => 13, 'colB' => 14, 'colC' => 15],
    ]);

    $select = $this->df->select('colA', 'colB', 'colC')
        ->where(fn(array $r): bool => $r['colB'] % 2 === 0) // Even numbers in colB only
        ->and(fn(array $r): bool => $r['colB'] < 14);
});

it('can use multiple conditions', function (): void {
    $select1 = $this->df->select('colA', 'colB', 'colC')
        ->where(fn(array $r): bool => $r['colB'] % 2 === 0) // Even numbers in colB only
        ->and(fn(array $r): bool => $r['colB'] < 14);

    // Equivalent to previous
    $select2 = $this->df->select('colA', 'colB', 'colC')
        ->where(fn(array $r): bool => $r['colB'] % 2 === 0) // Even numbers in colB only
        ->where(fn(array $r): bool => $r['colB'] < 14);

    expect($select1->get()->toArray())->toBe($select2->get()->toArray())->toBe([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});

it('cannot match any condition', function (): void {
    $select = $this->df->select('colA', 'colB', 'colC')
        ->and(fn(array $r): bool => $r['colB'] > 14);

    expect($select->get()->toArray())->toBe([]);
});

it('can use or condition', function (): void {
    $select = $this->df->select('colA', 'colB', 'colC')
        ->where(fn(array $r): bool => $r['colB'] % 2 === 0) // Even numbers in colB only
        ->or(fn(array $r): bool => $r['colA'] === 4);

    expect($select->get()->toArray())->toBe([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
        ['colA' => 13, 'colB' => 14, 'colC' => 15],
    ]);

    $select = $this->df->select('colA', 'colB', 'colC')
        ->where(fn(array $r): bool => $r['colB'] > 2) // Even numbers in colB only
        ->and(fn(array $r): bool => $r['colA'] % 2 === 0)->or(fn(array $r): bool => $r['colA'] === 13)
        ->and(fn(array $r): bool => $r['colA'] !== 4);

    expect($select->get()->toArray())->toBe([
        ['colA' => 10, 'colB' => 11, 'colC' => 12],
        ['colA' => 13, 'colB' => 14, 'colC' => 15],
    ]);
});

test('whereColumn', function (): void {
    $select1 = $this->df->select('colA')
        ->whereColumn('colB', 8);

    $select2 = $this->df->select('colA')
        ->whereColumn('colB', fn($v): bool => $v === 8);

    expect($select1->get()->toArray())->toBe($select2->get()->toArray())->toBe([
        ['colA' => 7],
    ]);
});
