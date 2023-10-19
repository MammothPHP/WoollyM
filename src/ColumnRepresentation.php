<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant;

use CondorcetPHP\Oliphant\Exceptions\{InvalidColumnException, PropertyNotExistException};
use Stringable;
use WeakReference;

class ColumnRepresentation implements Stringable
{
    protected readonly WeakReference $columnIndex;

    public function __construct(ColumnIndex $columnIndex)
    {
        $this->columnIndex = WeakReference::create($columnIndex);
    }

    public function isAlive(): bool
    {
        if ($this->columnIndex->get() === null) {
            return false;
        }

        return true;
    }

    // Implement property overloading
    public function __set(string $name, mixed $value): void {
        if ($name === 'values') {
            $this->setValues($value);
            return;
        }

        throw new PropertyNotExistException;
    }

    public function __get(string $name): mixed {
        if ($name === 'sum') {
            return $this->sum();
        }

        throw new PropertyNotExistException;
    }

    public function __isset(string $name): bool {
        if ($name === 'sum') {
            return true;
        }

        return false;
    }

    protected function isAliveorThrowInvalidColumnException(): void
    {
        $this->isAlive() || throw new InvalidColumnException;
    }

    public function getName(): string
    {
        $this->isAliveorThrowInvalidColumnException();

        return $this->columnIndex->get()->name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getDataFrame(): DataFrame
    {
        return $this->columnIndex->get()->df->get();
    }

    public function remove(): DataFrameCore
    {
        return $this->getDataFrame()->removeColumn($this->getName());
    }

    public function setValues(mixed $value): self
    {
        $this->isAliveorThrowInvalidColumnException();

        $this->getDataFrame()->setColumn($this->getName(), $value);

        return $this;
    }

    public function sum(): int|float
    {
        $r = 0;
        $columnName = $this->getName();

        foreach ($this->getDataFrame()->getColumn($columnName) as $value) {
            $r += $value[$columnName];
        }

        return $r;
    }
}
