<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Stats\Modules\Sum;

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
    $newDf = $this->df->select('colA', 'colB', 'colC')->limit(2)->export();
    expect($newDf)->toHaveCount(2);
    expect($newDf->toArray())->toBe([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
    ]);

    $newDf = $this->df->select('colA', 'colB', 'colC')->limit(2, 1)->export();
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

it('count records', function (): void {
    $select = $this->df->select('colC', 'colB');

    expect($select->countRecords())
        ->toBe(5)
        ->and($select->whereColumn('colA', fn($v) => $v > 1)->countRecords())
        ->toBe(4)
        ->and($select->limit(2)->countRecords())
        ->toBe(2)
        ->and($select->offset(3)->countRecords())
        ->toBe(1)
    ;
});

it('can be use a single condition', function (): void {
    $select = $this->df->select('colA', 'colB', 'colC')->where(fn(array $r): bool => $r['colB'] % 2 === 0); // Even numbers in colB only

    expect($select->export()->toArray())->toBe([
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

    expect($select1->export()->toArray())->toBe($select2->export()->toArray())->toBe([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});

it('cannot match any condition', function (): void {
    $select = $this->df->select('colA', 'colB', 'colC')
        ->and(fn(array $r): bool => $r['colB'] > 14);

    expect($select->export()->toArray())->toBe([]);
});

it('can use or condition', function (): void {
    $select = $this->df->select('colA', 'colB', 'colC')
        ->where(fn(array $r): bool => $r['colB'] % 2 === 0) // Even numbers in colB only
        ->or(fn(array $r): bool => $r['colA'] === 4);

    expect($select->export()->toArray())->toBe([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
        ['colA' => 13, 'colB' => 14, 'colC' => 15],
    ]);

    $select = $this->df->select('colA', 'colB', 'colC')
        ->where(fn(array $r): bool => $r['colB'] > 2) // Even numbers in colB only
        ->and(fn(array $r): bool => $r['colA'] % 2 === 0)->or(fn(array $r): bool => $r['colA'] === 13)
        ->and(fn(array $r): bool => $r['colA'] !== 4);

    expect($select->export()->toArray())->toBe([
        ['colA' => 10, 'colB' => 11, 'colC' => 12],
        ['colA' => 13, 'colB' => 14, 'colC' => 15],
    ]);
});

test('whereColumn', function (): void {
    $select1 = $this->df->select('colA')
        ->whereColumn('colB', equal: 8);

    $select2 = $this->df->select('colA')
        ->whereColumn('colB', fn($v): bool => $v === 8);

    expect($select1->export()->toArray())->toBe($select2->export()->toArray())->toBe([
        ['colA' => 7],
    ]);
});

test('whereColumn contain', function (): void {
    $df = new DataFrame([
        ['composer' => 'Ravel'],
        ['composer' => 'Debussy'],
        ['composer' => 'Koechlin'],
    ]);

    $select = $df->selectAll()->whereColumn('composer', contain: 'D');
    expect($select->export()->toArray())->toBe([
        ['composer' => 'Debussy'],
    ]);

    $df[] = ['composer' => 41];

    $select = $df->selectAll()->whereColumn('composer', contain: 'Ko');
    expect($select->export()->toArray())->toBe([
        ['composer' => 'Koechlin'],
    ]);

    $select = $df->selectAll()->whereColumn('composer', contain: '41');
    expect($select->export()->toArray())->toBe([
        ['composer' => 41],
    ]);

    $df[] = ['composer' => true];

    // True dont' must dont must to match 1
    $select = $df->selectAll()->whereColumn('composer', contain: '1');
    expect($select->export()->toArray())->toBe([
        ['composer' => 41],
    ]);

    $df[] = ['composer' => $stringable = new class {
        public function __toString(): string
        {
            return 'Debussy';
        }
    }];

    $select = $df->selectAll()->whereColumn('composer', contain: 'De');
    expect($select->export()->toArray())->toBe([
        ['composer' => 'Debussy'],
        ['composer' => $stringable],
    ]);
});

test('whereColumn match', function (): void {
    $df = new DataFrame([
        ['composer' => 'Ravel'],
        ['composer' => 'Debussy'],
        ['composer' => 'Koechlin'],
    ]);

    $select = $df->selectAll()->whereColumn('composer', match: '/Deb|el/');
    expect($select->export()->toArray())->toBe([
        ['composer' => 'Ravel'],
        ['composer' => 'Debussy'],
    ]);

    $df[] = ['composer' => 41];
    $df[] = ['composer' => $stringable = new class {
        public function __toString(): string
        {
            return 'Debussy';
        }
    }];

    $select = $df->selectAll()->whereColumn('composer', match: '/41|Debussy/');
    expect($select->export()->toArray())->toBe([
        ['composer' => 'Debussy'],
        ['composer' => 41],
        ['composer' => $stringable],
    ]);

});

test('whereKeyBetween', function (): void {
    $select = $this->df->selectAll()->whereKeyBetween(1, 3);

    expect($select->countRecords())->toBe(3)->and($select->toArray())->toHaveCount(3)->toHaveKeys([1, 2, 3]);
    expect($select->whereKeyBetween(0, null)->countRecords())->toBe(5)->and($select->toArray())->toHaveCount(5)->toHaveKeys([0, 1, 2, 3, 4]);
    expect($select->whereKeyBetween(1, 4)->countRecords())->toBe(4)->and($select->toArray())->toHaveCount(4)->toHaveKeys([1, 2, 3, 4]);
    expect($select->whereKeyBetween(2, 3)->countRecords())->toBe(2)->and($select->toArray())->toHaveCount(2)->toHaveKeys([2, 3]);

    expect($select->resetWhere()->countRecords())->toBe(5);
});

test('groupBy', function (): void {
    $df = new DataFrame([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
        ['a' => 4, 'b' => 5, 'c' => 9],
    ]);

    $grouped = $df->selectAll()
        ->whereColumn('a', fn(int $v) => $v % 2 === 0) // a is even
        ->groupBy('a', Sum::col('b'));

    expect($grouped->toArray())->tobe([
        ['a' => 2, 'b' => 8],
        ['a' => 4, 'b' => 5],
    ]);
});
