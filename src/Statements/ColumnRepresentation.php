<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

use Closure;
use MammothPHP\WoollyM\{ColumnIndex, DataFrame, DataType};
use MammothPHP\WoollyM\Exceptions\DataFrameException;
use Override;
use Stringable;
use WeakReference;

class ColumnRepresentation extends FixedSelect implements Stringable
{
    protected readonly WeakReference $columnIndex;

    public function __construct(ColumnIndex $columnIndex)
    {
        $this->columnIndex = WeakReference::create($columnIndex);
        $this->setLinkedDataFrame($columnIndex->df->get());
    }

    #[Override]
    public function isAlive(): bool
    {
        return $this->columnIndex->get() !== null && parent::isAlive();
    }

    #[Override]
    public function __get(string $name): mixed
    {
        if ($name === 'name') {
            return $this->getName();
        }

        return parent::__get($name);
    }

    // Implement property & methods overloading
    #[Override]
    public function __set(string $name, mixed $value): void
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($name === 'values') {
            $this->set($value);

            return;
        }

        parent::__set($name, $value);
    }

    public function getName(): string
    {
        $this->isAliveOrThrowInvalidSelectException();

        return $this->columnIndex->get()->getName();
    }

    #[Override]
    public function getSelect(): array
    {
        return [$this->getName()];
    }

    public function __toString(): string
    {
        return $this->getName();
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

        $this->columnIndex->get()->setForcedType($type);

        return $this;
    }

    public function remove(): DataFrame
    {
        return $this->getLinkedDataFrame()->removeColumn($this->getName());
    }

    public function rename(string $to): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        $this->columnIndex->get()->setName($to);

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

        if (\count($df) !== \count($this->getLinkedDataFrame())) {
            $msg = 'Source and target DataFrames must have identical number ';
            $msg .= 'of rows.';

            throw new DataFrameException($msg);
        }

        $this->apply(fn(mixed $value, int $position): mixed => current($df->getRecord($position)));
    }

    public function apply(Closure $f): void
    {
        $target_df = $this->getLinkedDataFrame();
        $target_colName = $this->columnIndex->get()->getName();

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
