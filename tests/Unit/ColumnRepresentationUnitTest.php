<?php

declare(strict_types=1);

use CondorcetPHP\Oliphant\{ColumnIndex, ColumnRepresentation, DataFrame};
use CondorcetPHP\Oliphant\Exceptions\{InvalidColumnException, PropertyNotExistException};

beforeEach(function (): void {
    $this->dataFrame = new DataFrame;
    $this->columnIndex = new ColumnIndex('col1', $this->dataFrame);
    $this->columnRepresentation = new ColumnRepresentation($this->columnIndex);
});

it('has coherent names', function (): void {
    expect($this->columnRepresentation->getName())
        ->toBe((string) $this->columnRepresentation)
        ->toBe((string) $this->columnIndex)
        ->toBe('col1')
    ;
});

it('has coherent references', function (): void {
    expect($this->columnRepresentation->getDataFrame())
        ->toBe($this->dataFrame)
        ->toBe($this->columnIndex->df->get());

});

test('ColumnRepresentation throw exception if the source no longer exists', function (): void {
    $this->columnIndex = null;
    $this->columnRepresentation->getName();
})->throws(InvalidColumnException::class);

it('throw error if dynamic property not exist on get', fn() => $this->columnRepresentation->foo)->throws(PropertyNotExistException::class);
it('throw error if dynamic property not exist on set', fn() => $this->columnRepresentation->foo = 42)->throws(PropertyNotExistException::class);