<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{PropertyNotExistException, UnavailableMethodInContext};
use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Statements\StatementClause;

beforeEach(function (): void {
    $this->df = DataFrame::fromArray([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});

it('can retrieve a simple record', function (): void {
    expect($this->df->select()->record(1))->toBeSameRecord($this->df[1]);

    $stmt = $this->df->select()->whereColumn('colA', equal: 1);

    expect($stmt->countRecords())->toBe(1);
    expect($stmt->record(1))->toBeSameRecord($this->df[1]);
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
    expect($select->config(StatementClause::SELECT))->toBe(['colA', 'colB']);

    $select->select('colC');
    expect($select->config(StatementClause::SELECT))->toBe(['colA', 'colB', 'colC']);

    $select->replaceSelect('colA');
    expect($select->config(StatementClause::SELECT))->toBe(['colA']);

    $select->select('colB', 'colC');
    expect($select->config(StatementClause::SELECT))->toBe(['colA', 'colB', 'colC']);

    $select->resetSelect();
    expect($select->config(StatementClause::SELECT))->toBe([]);
});

it('support whereColumn constructor', function (): void {
    $select = $this->df->select('colA')->whereColumn('colB', 42)->whereColumn('colC', fn(mixed $v): bool => $v > 1);

    expect($select->config(StatementClause::WHERE))->toBeArray()->toHaveCount(2);
    expect($select->config(StatementClause::WHERE)[1][0])->toBeInstanceOf(Closure::class);
});

it('support where constructor', function (): void {
    $select = $this->df->select('colA');

    $c1 = fn() => true;

    $select->where($c1);
    expect($select->config(StatementClause::WHERE))->toBe([[$c1]]);
    $select->where($c1);
    expect($select->config(StatementClause::WHERE))->toBe([[$c1], [$c1]]);
    $select->and($c1);
    expect($select->config(StatementClause::WHERE))->toBe([[$c1], [$c1], [$c1]]);

    $select->or($c1);
    expect($select->config(StatementClause::WHERE))->toBe([[$c1], [$c1], [$c1, $c1]]);

    $select->and($c1);
    expect($select->config(StatementClause::WHERE))->toBe([[$c1], [$c1], [$c1, $c1], [$c1]]);

    $select->resetWhere();
    expect($select->config(StatementClause::WHERE))->toBe([]);
});

it('support limit and offset constructor', function (): void {
    $select = $this->df->select('colA');

    $select->limit(5, 10);
    expect($select->config(StatementClause::LIMIT))->toBe(5);
    expect($select->config(StatementClause::OFFSET))->toBe(10);

    $select->offset(7);
    expect($select->config(StatementClause::LIMIT))->toBe(5);
    expect($select->config(StatementClause::OFFSET))->toBe(7);

    $select->limit(42);
    expect($select->config(StatementClause::LIMIT))->toBe(42);
    expect($select->config(StatementClause::OFFSET))->toBe(0);

    $select->offset(8);
    expect($select->config(StatementClause::OFFSET))->toBe(8);

    $select->resetLimit();
    expect($select->config(StatementClause::LIMIT))->toBeNull();
    expect($select->config(StatementClause::OFFSET))->toBe(0);

    $select->offset(9);
    expect($select->config(StatementClause::OFFSET))->toBe(9);

    $select->resetOffset();
    expect($select->config(StatementClause::OFFSET))->toBe(0);
});

test('reset', function (): void {
    $c1 = fn() => true;
    $select = $this->df->select('colA')->where($c1)->and($c1)->or($c1)->limit(4)->offset(5);

    expect($select->reset())->toBe($select);

    expect($select->config(StatementClause::SELECT))->toBe([]);
    expect($select->config(StatementClause::WHERE))->toBe([]);
    expect($select->config(StatementClause::LIMIT))->toBeNull();
    expect($select->config(StatementClause::OFFSET))->toBe(0);
});

test('selectAll can be reset', function (): void {
    $c1 = fn() => true;
    $select = $this->df->selectAll()->where($c1)->and($c1)->or($c1)->limit(4)->offset(5);

    expect($select->reset())->toBe($select);

    expect($select->config(StatementClause::WHERE))->toBe([]);
    expect($select->config(StatementClause::LIMIT))->toBeNull();
    expect($select->config(StatementClause::OFFSET))->toBe(0);
});

it('support cloning (dataFrame tests)', function (): void {
    $select1 = $this->df->select('colA');
    $select2 = clone $select1;
    expect($select1->getLinkedDataFrame())->toBe($this->df)->toBe($select2->getLinkedDataFrame());

    // $this->df = new DataFrame;

    // expect(fn() => $select1->getLinkedDataFrame())->toThrow(InvalidSelectException::class);
    // expect(fn() => $select2->getLinkedDataFrame())->toThrow(InvalidSelectException::class);

    // expect(fn() => $select2->select('colB'))->toThrow(InvalidSelectException::class);
    // expect(fn() => $select2->where(fn() => true))->toThrow(InvalidSelectException::class);
    // expect(fn() => $select2->limit(42))->toThrow(InvalidSelectException::class);
    // expect(fn() => $select2->offset(42))->toThrow(InvalidSelectException::class);
    // expect(fn() => $select2->export())->toThrow(InvalidSelectException::class);
});

it('support cloning (selections tests)', function (): void {
    $select1 = $this->df->select('colA');
    $select2 = clone $select1;

    expect($select1->getSelect())->toBe($select2->getSelect())->toBe(['colA']);

    $select2->select('colA', 'colB');
    expect($select1->getSelect())->not->toBe($select2->getSelect());
});

it('can produce a new DataFrame', function (): void {
    $newDf = $this->df->select('colA', 'colB', 'colC')->export();
    expect($newDf->toArray())->toEqual($this->df->toArray());

    $newDf = $this->df->select('colA', 'colB')->export();
    expect($newDf->toArray())->not->toEqual($this->df->toArray());
});

it('can select all', function (): void {
    $select = $this->df->selectAll();

    expect($select->config(StatementClause::SELECT))->toBe(['colA', 'colB', 'colC']);
});

it('keep select all', function (): void {
    $select = $this->df->selectAll();
    $this->df->addColumn('colD');

    expect($select->config(StatementClause::SELECT))->toBe(['colA', 'colB', 'colC', 'colD']);
});

it('throw an exception if module method not exist', function (): void {
    $this->df->selectAll()->bidule();
})->throws(BadMethodCallException::class, 'Call to undefined method bidule()');

it('throw an exception if module property not exist', function (): void {
    $this->df->selectAll()->bidule;
})->throws(PropertyNotExistException::class, 'Call to undefined property bidule');

test('selectAll cannot be reset', function (): void {
    $this->df->selectAll()->resetSelect();
})->throws(UnavailableMethodInContext::class);

test('selectAll cannot be replaced', function (): void {
    $this->df->selectAll()->replaceSelect();
})->throws(UnavailableMethodInContext::class);
