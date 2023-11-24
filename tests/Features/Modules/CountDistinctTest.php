<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Stats\Modules\CountDistinct;

beforeEach(function (): void {
    $obj1 = new class {};
    $obj2 = new class {};

    $this->df = new DataFrame([
        ['a' => 1,      'b' => null, 'c' => 3,       'd' => 'foo'],
        ['a' => 4,      'b' => '',   'c' => false,   'd' => 'bar'],
        ['a' => 7,      'b' => 8,    'c' => 4,       'd' => 'foo'],
        ['a' => 4,      'b' => null, 'c' => 3,       'd' => true],
        ['a' => 4.42,   'b' => null, 'c' => 5.11,    'd' => 4.42],
        ['a' => $obj1,   'b' => $obj2, 'c' => $obj1, 'd' => $obj2],
    ]);
});

test('many counts', function (): void {
    expect($this->df->col('a')->countDistinct())->toBe($this->df->col('a')->countDistinct)->toBe(5);
    expect($this->df->col('b')->countDistinct())->toBe($this->df->col('b')->countDistinct)->toBe(3);
    expect($this->df->col('c')->countDistinct())->toBe($this->df->col('c')->countDistinct)->toBe(5);
    expect($this->df->col('d')->countDistinct())->toBe($this->df->col('d')->countDistinct)->toBe(5);

    expect($this->df->selectAll()->countDistinct())->toBe($this->df->selectAll()->countDistinct)->toBe(14);
});

test('hash & collision', function (): void {
    $h = fn(string $v): string => hash('sha3-384', $v, true);

    $df = new DataFrame([
        ['a' => $h('a'), 'b' => $h('b')],
        ['a' => $h('a')],
    ]);

    $select = $df->selectAll();

    expect($select->countDistinct)->toBe(2);

    $collision = hash(CountDistinct::HASH_ALGO, $h('a'), true); // Protection against deliberate collision must work
    $df[] = ['a' => $collision];

    expect($select->countDistinct)->toBe(3);
});