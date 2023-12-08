<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;

beforeEach(function (): void {
    $this->expectedArray1 = [
        ['colC' => 1, 'colB' => 2, 'colA' => 3],
    ];

    $this->df = DataFrame::fromArray($this->expectedArray1);
});

it('keep correct order of column', function (): void {
    $df = $this->df;

    $expected = [0 => 'colC', 1 => 'colB', 2 => 'colA'];
    expect($df->columnsNames())->toBe($expected);
    expect($df->columns())->toEqual($expected);
    expect($df->toArray())->toBe($this->expectedArray1);

    $df->removeColumn('colB')->addColumn('colB');

    $expected = [0 => 'colC', 2 => 'colA', 3 => 'colB'];
    expect($df->columnsNames())->toBe($expected);
    expect($df->columns())->toEqual($expected);
    expect($df->toArray())->toBe([['colC' => 1, 'colA' => 3]]);
});
