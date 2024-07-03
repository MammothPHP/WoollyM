<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Select;

use Closure;
use Exception;
use MammothPHP\WoollyM\{ColumnIndex, DataFrame, DataType};
use MammothPHP\WoollyM\Exceptions\{DataFrameException, InvalidSelectException};
use Override;
use Stringable;
use WeakReference;

/**
 * A special Select object restricted to a single column, implementing extra columns manipulation methods.
 */
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

    /**
     * @internal
     */
    #[Override]
    public function __get(string $name): mixed
    {
        if ($name === 'name') {
            return $this->getName();
        }

        return parent::__get($name);
    }

    /**
     * @internal
     */
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

    /**
     * Return the column name
     * @throws InvalidSelectException
     */
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

    /**
     * Convert all colonne data to a specified type
     */
    public function type(DataType $type, array|string|null $fromDateFormat = null, ?string $toDateFormat = null): self
    {
        $this->apply(static fn(mixed $value): mixed => $type->convert($value, $fromDateFormat, $toDateFormat));

        return $this;
    }

    /**
     * @todo
     * @throws InvalidSelectException
     */
    public function enforceType(?DataType $type): self
    {
        if ($type !== null) {
            $this->type($type);
        }

        $this->columnIndex->get()->setForcedType($type);

        return $this;
    }

    /**
     * Remove the column from DataFrame
     * @throws InvalidSelectException
     * @throws Exception
     */
    public function remove(): void
    {
        $this->getLinkedDataFrame()->removeColumn($this->getName());
    }

    /**
     * Rename column
     * @throws InvalidSelectException
     */
    public function rename(string $to): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        $this->columnIndex->get()->setName($to);

        return $this;
    }

    /**
     * Set a value or apply a closure to all value selected.
     * @param $set - The value to set or the closure to apply.
     * @throws InvalidSelectException
     * @throws DataFrameException
     */
    public function set(mixed $set): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($set instanceof DataFrame) {
            $this->setDataFrame($set);
        } elseif ($set instanceof Closure) {
            $this->apply($set);
        } else {
            $this->setColumnValue($set);
        }

        return $this;
    }

    /**
     * Merge DataFrame to the column value from the beginning.
     * @param $df - Single column dataFrame
     * @throws DataFrameException
     * @throws InvalidSelectException
     */
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

        $this->apply(fn(mixed $value, int $position): mixed => current($df->getRecordAsArray($position)));
    }

    /**
     * Apply a closure return to all selected elements.
     * @throws InvalidSelectException
     */
    public function apply(Closure $f): void
    {
        $target_df = $this->getLinkedDataFrame();
        $target_colName = $this->columnIndex->get()->getName();

        foreach ($target_df->getRecordsAsArrayIterator() as $i => $record) {
            $record[$target_colName] = $f($record[$target_colName] ?? null, $i);
            $target_df[$i] = $record;
        }
    }

    /**
     * Set value to all selected elements.
     * @throws InvalidSelectException
     */
    public function setColumnValue(mixed $value): void
    {
        $this->apply(static fn(): mixed => $value);
    }
}
