<?php

declare(strict_types=1);
use CondorcetPHP\Oliphant\DataFrame;
use CondorcetPHP\Oliphant\Exceptions\DataFrameException;

beforeEach(function (): void {
    $this->input = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $this->df = DataFrame::fromArray($this->input);
});

test('invalid column', function (): void {
    $this->expectException('CondorcetPHP\Oliphant\Exceptions\InvalidColumnException');
    $this->df->col('foo');
});

test('remove non existent column', function (): void {
    $this->df->removeColumn('foo');
})->throws(DataFrameException::class);

test('invalid offset set1', function (): void {
    $this->df->col('foo')->setValues($this->df);
})->throws(DataFrameException::class);

test('invalid offset set2', function (): void {
    $df = $this->df;
    $df2 = DataFrame::fromArray([['a' => 1, 'b' => 2, 'c' => 3]]);

    $df->col('a')->setValues($df2->col('a')->asDataFrame);
})->throws(DataFrameException::class);
