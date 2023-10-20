<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant;

use CondorcetPHP\Oliphant\Exceptions\{InvalidColumnException, MethodNotExistException, PropertyNotExistException};
use CondorcetPHP\Oliphant\Stats\Modules;
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

    // Implement property & methods overloading
    public function __set(string $name, mixed $value): void
    {
        $this->isAliveorThrowInvalidColumnException();

        if ($name === 'values') {
            $this->setValues($value);

            return;
        }

        throw new PropertyNotExistException;
    }

    public function __get(string $name): mixed
    {
        $this->isAliveorThrowInvalidColumnException();

        if ($module = Modules::getColumnStatsPropertyModule($name)) {
            return $module->executeProperty($this);
        }

        throw new PropertyNotExistException;
    }

    public function __isset(string $name): bool
    {
        $this->isAliveorThrowInvalidColumnException();

        return Modules::getColumnStatsPropertyModule($name) ? true : false;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $this->isAliveorThrowInvalidColumnException();

        if ($module = Modules::getColumnStatsMethodModule($name)) {
            return $module->executeMethod($this, $arguments);
        }

        throw new MethodNotExistException;
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
        $this->isAliveorThrowInvalidColumnException();

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
}
