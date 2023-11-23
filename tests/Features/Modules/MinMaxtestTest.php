<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;

beforeEach(function (): void {
    $this->describeKeys = ['count', 'countNumerics', 'size', 'sum', 'mean', 'max', 'min'];

    $this->df = new DataFrame([
        ['a' => 4, 'b' => null,  'c' => false,  'd' => 42],
        ['a' => 1, 'b' => false, 'c' => true,   'd' => '42'],
        ['a' => 7, 'b' => null,  'c' => 0,      'd' => true],
        ['a' => 6, 'b' => null,  'c' => null,   'd' => false],
    ]);
});

test('equivalence', function (): void {
    $select = $this->df->selectAll();

    expect($select->max)->toBe($select->max())->toBe(42);
    expect($select->min)->toBe($select->min())->toBeNull();
});

test('various base comparaisons', function (): void {
    $select = $this->df->select('a');
    expect($select->max)->toBe(7)->and($select->min)->toBe(1);

    $select = $this->df->select('b');
    expect($select->max)->toBeFalse->and($select->min)->toBeNull;

    $select = $this->df->select('c');
    expect($select->max)->toBe(0)->and($select->min)->toBeNull;

    $select = $this->df->select('d');
    expect($select->max)->toBe(42)->and($select->min)->toBeFalse;
});
