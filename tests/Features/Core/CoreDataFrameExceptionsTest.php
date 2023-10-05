<?php

declare(strict_types=1);
use CondorcetPHP\Oliphant\DataFrame;

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
    $this->df['foo'];
});

test('remove non existent column', function (): void {
    $this->expectException('CondorcetPHP\Oliphant\Exceptions\DataFrameException');
    $this->df->removeColumn('foo');
});
test('invalid offset set1', function (): void {
    $df = $this->df;

    $this->expectException('CondorcetPHP\Oliphant\Exceptions\DataFrameException');
    $df['foo'] = $df;
});

test('invalid offset set2', function (): void {
    $df = $this->df;
    $df2 = DataFrame::fromArray([['a' => 1, 'b' => 2, 'c' => 3]]);

    $this->expectException('CondorcetPHP\Oliphant\Exceptions\DataFrameException');
    $df['a'] = $df2['a'];
});
