<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\CacheStatus;

beforeEach(function (): void {
    $this->df = DataFrame::fromArray([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});

it('can can be count with Pest/PHPunit', function (): void {
    expect(\count($this->df->select()))->toBe(3);
    expect($this->df->select())->toHaveCount(3);
});

it('has a cache status', function(): void {
    $stmt = $this->df->select('colA')->whereColumn('colA', equal: 1)->groupBy('colA');

    expect($stmt->cacheStatus)->toBe(CacheStatus::UNUSED);

    $stmt->export();
    expect($stmt->cacheStatus)->toBe(CacheStatus::SET);
    expect($stmt)->toHaveCount(1);

    $stmt->resetWhere();
    expect($stmt->cacheStatus)->toBe(CacheStatus::UNUSED);
    $stmt->whereKeyBetween(0,2);

    expect($stmt->cacheStatus)->toBe(CacheStatus::UNUSED);

    expect($stmt)->toHaveCount(3);
    expect($stmt->cacheStatus)->toBe(CacheStatus::SET);

    expect(fn() => $stmt->cacheStatus = CacheStatus::SET)->toThrow('$cacheStatus is read-only');
});