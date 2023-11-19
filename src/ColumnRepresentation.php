<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use MammothPHP\WoollyM\Exceptions\{DataFrameException, InvalidSelectException, MethodNotExistException, PropertyNotExistException};
use MammothPHP\WoollyM\Stats\Modules;
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
        return $this->columnIndex->get() !== null;
    }

    // Implement property & methods overloading
    public function __set(string $name, mixed $value): void
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($name === 'values') {
            $this->set($value);

            return;
        }

        throw new PropertyNotExistException;
    }

    public function __get(string $name): mixed
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($module = Modules::getColumnStatsPropertyModule($name)) {
            return $module->executeProperty($this);
        }

        throw new PropertyNotExistException;
    }

    public function __isset(string $name): bool
    {
        $this->isAliveOrThrowInvalidSelectException();

        return Modules::getColumnStatsPropertyModule($name) ? true : false;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($module = Modules::getColumnStatsMethodModule($name)) {
            return $module->executeMethod($this, $arguments);
        }

        throw new MethodNotExistException;
    }

    protected function isAliveOrThrowInvalidSelectException(): void
    {
        $this->isAlive() || throw new InvalidSelectException;
    }

    public function getName(): string
    {
        $this->isAliveOrThrowInvalidSelectException();

        return $this->columnIndex->get()->name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getLinkedDataFrame(): DataFrameCore
    {
        $this->isAliveOrThrowInvalidSelectException();

        return $this->columnIndex->get()->df->get();
    }

    public function type(DataType $type, array|string|null $fromDateFormat = null, ?string $toDateFormat = null): self
    {
        $this->apply(fn(mixed $value): mixed => $type->convert($value, $fromDateFormat, $toDateFormat));

        return $this;
    }

    public function enforceType(?DataType $type): self
    {
        if ($type !== null) {
            $this->type($type);
        }

        $this->columnIndex->get()->forcedType = $type;

        return $this;
    }

    public function remove(): DataFrameCore
    {
        return $this->getLinkedDataFrame()->removeColumn($this->getName());
    }

    public function rename(string $to): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        $this->columnIndex->get()->name = $to;

        return $this;
    }

    public function set(mixed $value): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($value instanceof DataFrame) {
            $this->setDataFrame($value);
        } elseif ($value instanceof Closure) {
            $this->apply($value);
        } else {
            $this->setColumnValue($value);
        }

        return $this;
    }

    private function setDataFrame(DataFrame $df): void
    {
        if ($df->countColumns() !== 1) {
            $msg = 'Can only set a new column from a DataFrame with a single ';
            $msg .= 'column.';

            throw new DataFrameException($msg);
        }

        if (\count($df) != \count($this->getLinkedDataFrame())) {
            $msg = 'Source and target DataFrames must have identical number ';
            $msg .= 'of rows.';

            throw new DataFrameException($msg);
        }

        $this->apply(fn(mixed $value, int $position): mixed => current($df->getRecord($position)));
    }

    public function apply(Closure $f): void
    {
        $target_df = $this->getLinkedDataFrame();
        $target_colName = $this->columnIndex->get()->name;

        foreach ($target_df as $i => $row) {
            $row[$target_colName] = $f($row[$target_colName] ?? null, $i);
            $target_df[$i] = $row;
        }
    }

    public function setColumnValue(mixed $value): void
    {
        $this->apply(fn(): mixed => $value);
    }
}
