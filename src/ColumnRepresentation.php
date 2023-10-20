<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant;

use Closure;
use CondorcetPHP\Oliphant\Exceptions\{DataFrameException, InvalidColumnException, MethodNotExistException, PropertyNotExistException};
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

    public function getDataFrame(): DataFrameCore
    {
        $this->isAliveorThrowInvalidColumnException();

        return $this->columnIndex->get()->df->get();
    }

    public function asDataFrame(): DataFrameCore {
        $this->isAliveorThrowInvalidColumnException();

        $data = [];
        $colName = $this->getName();

        foreach ($this->getDataFrame() as $row) {
            $data[] = [$colName => $row[$colName]];
        }

        return new DataFrame($data);
    }

    public function remove(): DataFrameCore
    {
        return $this->getDataFrame()->removeColumn($this->getName());
    }

    public function rename(string $to): self
    {
        $this->isAliveorThrowInvalidColumnException();

        $this->columnIndex->get()->name = $to;

        return $this;
    }

    public function setValues(mixed $value): self
    {
        $this->isAliveorThrowInvalidColumnException();

        if ($value instanceof DataFrame) {
            $this->columnSetDataFrame($value);
        } elseif ($value instanceof Closure) {
            $this->columnSetClosure($value);
        } else {
            $this->setColumnValue($value);
        }

        return $this;
    }

        /**
     * Allows user set DataFrame columns from a single-column DataFrame.
     *      ie:
     *          $df['bar'] = $df['foo'];
     *
     * @internal
     * @param  DataFrame $df
     * @throws DataFrameException
     * @since  0.1.0
     */
    private function columnSetDataFrame(DataFrame $df): void
    {
        if ($df->countColumns() !== 1) {
            $msg = 'Can only set a new column from a DataFrame with a single ';
            $msg .= 'column.';

            throw new DataFrameException($msg);
        }

        if (\count($df) != \count($this->getDataFrame())) {
            $msg = 'Source and target DataFrames must have identical number ';
            $msg .= 'of rows.';

            throw new DataFrameException($msg);
        }

        $target_df = $this->getDataFrame();
        $target_index = $this->columnIndex->get();

        foreach ($target_df as $i => $row) {
            $target_df[$i][$target_index] = current($df->getIndex($i));
        }
    }

    /**
     * Allows user set DataFrame columns from a Closure.
     *      ie:
     *          $df['foo'] = function ($foo) { return $foo + 1; };
     *
     * @internal
     * @param Closure $f
     * @since 0.1.0
     */
    public function columnSetClosure(Closure $f): void
    {
        $target_df = $this->getDataFrame();
        $target_index = $this->columnIndex->get();
        $target_name = $target_index->name;

        foreach ($target_df as $i => $row) {
            $target_df[$i][$target_index] = $f($row[$target_name]);
        }
    }

    /**
     * Allows user set DataFrame columns from a variable and add new rows to Dataframe
     *      ie:
     *          $df['foo'] = 'bar';
     *
     *          $df[] = [ 'foo' => 1, 'bar' => 2, 'baz' => 3 ];
     *
     * @internal
     * @param $value
     * @since 0.1.0
     */
    public function setColumnValue(mixed $value): void
    {
        $this->columnSetClosure(fn (): mixed => $value);
    }
}
