<?php

declare(strict_types=1);

use MammothPHP\WoollyM\{DataFrame, Select, SelectParam};
use MammothPHP\WoollyM\Exceptions\InvalidSelectException;

beforeEach(function (): void {
    $this->df = DataFrame::fromArray([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});

it('return a select object', function (): void {
    $select1 = $this->df->select('colA');

    expect($select1)->toBeInstanceOf(Select::class);

    $select2 = $this->df->select(); // Whithout argument

    expect($select2)->toBeInstanceOf(Select::class);
    expect($select2)->not->toBe($select1);

    expect($select1->getLinkedDataFrame())->toBe($select2->getLinkedDataFrame())->toBe($this->df);
});

it('support select constructor', function (): void {
    $select = $this->df->select('colA', 'colB');
    expect($select->config(SelectParam::SELECT))->toBe(['colA', 'colB']);

    $select->select('colC');
    expect($select->config(SelectParam::SELECT))->toBe(['colA', 'colB', 'colC']);

    $select->replaceSelect('colA');
    expect($select->config(SelectParam::SELECT))->toBe(['colA']);

    $select->select('colB', 'colC');
    expect($select->config(SelectParam::SELECT))->toBe(['colA', 'colB', 'colC']);

    $select->resetSelect();
    expect($select->config(SelectParam::SELECT))->toBe([]);
});

it('support where constructor', function (): void {
    $select = $this->df->select('colA');

    $c1 = fn() => true;

    $select->where($c1);
    expect($select->config(SelectParam::WHERE))->toBe([[$c1]]);
    $select->where($c1);
    expect($select->config(SelectParam::WHERE))->toBe([[$c1], [$c1]]);
    $select->and($c1);
    expect($select->config(SelectParam::WHERE))->toBe([[$c1], [$c1], [$c1]]);

    $select->or($c1);
    expect($select->config(SelectParam::WHERE))->toBe([[$c1], [$c1], [$c1, $c1]]);

    $select->and($c1);
    expect($select->config(SelectParam::WHERE))->toBe([[$c1], [$c1], [$c1, $c1], [$c1]]);

    $select->resetWhere();
    expect($select->config(SelectParam::WHERE))->toBe([]);
});

it('support limit and offset constructor', function (): void {
    $select = $this->df->select('colA');

    $select->limit(5, 10);
    expect($select->config(SelectParam::LIMIT))->toBe(5);
    expect($select->config(SelectParam::OFFSET))->toBe(10);

    $select->offset(7);
    expect($select->config(SelectParam::LIMIT))->toBe(5);
    expect($select->config(SelectParam::OFFSET))->toBe(7);

    $select->limit(42);
    expect($select->config(SelectParam::LIMIT))->toBe(42);
    expect($select->config(SelectParam::OFFSET))->toBe(0);

    $select->offset(8);
    expect($select->config(SelectParam::OFFSET))->toBe(8);

    $select->resetLimit();
    expect($select->config(SelectParam::LIMIT))->toBeNull();
    expect($select->config(SelectParam::OFFSET))->toBe(0);

    $select->offset(9);
    expect($select->config(SelectParam::OFFSET))->toBe(9);

    $select->resetOffset();
    expect($select->config(SelectParam::OFFSET))->toBe(0);
});

test('the reset', function (): void {
    $c1 = fn() => true;
    $select = $this->df->select('colA')->where($c1)->and($c1)->or($c1)->limit(4)->offset(5);

    expect($select->reset())->toBe($select);

    expect($select->config(SelectParam::SELECT))->toBe([]);
    expect($select->config(SelectParam::WHERE))->toBe([]);
    expect($select->config(SelectParam::LIMIT))->toBeNull();
    expect($select->config(SelectParam::OFFSET))->toBe(0);
});

it('support cloning', function (): void {
    $select1 = $this->df->select('colA');
    $select2 = clone $select1;
    expect($select1->getLinkedDataFrame())->toBe($this->df)->toBe($select2->getLinkedDataFrame());

    $this->df = new DataFrame;

    expect(fn() => $select1->getLinkedDataFrame())->toThrow(InvalidSelectException::class);
    expect(fn() => $select2->getLinkedDataFrame())->toThrow(InvalidSelectException::class);

    expect(fn() => $select2->select('colB'))->toThrow(InvalidSelectException::class);
    expect(fn() => $select2->where(fn() => true))->toThrow(InvalidSelectException::class);
    expect(fn() => $select2->limit(42))->toThrow(InvalidSelectException::class);
    expect(fn() => $select2->offset(42))->toThrow(InvalidSelectException::class);
    expect(fn() => $select2->get())->toThrow(InvalidSelectException::class);
});
