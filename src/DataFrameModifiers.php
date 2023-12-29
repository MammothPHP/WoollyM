<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use MammothPHP\WoollyM\DataDrivers\SortableDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\SortNotSupportedByDriverException;
use MammothPHP\WoollyM\Exceptions\NotModifiedRecord;
use MammothPHP\WoollyM\Statements\Modify\Modify;

abstract class DataFrameModifiers extends DataFrameStatements
{
    /* *****************************************************************************************************************
     ******************************************* Copy ******************************************************************
     ******************************************************************************************************************/

    /**
     * Return a Copy object, methods will provide new DataFrame objects.
     */
    public function copy(DataFrame $to = new DataFrame): Copy
    {
        return new Copy($this, $to);
    }

    /* *****************************************************************************************************************
     ******************************************* Modify ****************************************************************
     ******************************************************************************************************************/

    /**
     * Return a Copy object, methods will provide new DataFrame objects.
     */
    public function modify(): Modify
    {
        return new Modify($this);
    }

    /**
     * Sort column order using a closure. Then retrieve records will respect the new order.
     * @param $callback - If null, sort will be alphabetic. For closure, example fn(string $a, string $b): int => $a <=> $b;
     */
    public function sortColumns(?Closure $callback = null): self
    {
        $callback ??= fn(string $a, string $b): int => $a <=> $b;
        $finalCallback = fn(ColumnIndex $a, ColumnIndex $b): int => $callback($a->getName(), $b->getName()); // protected ColumnIndex leak

        uasort($this->columnIndexes, $finalCallback);
        $this->clearColumnsCache();

        return $this;
    }

    /**
     * Applies a user-defined function to each record of the DataFrame. The parameters of the function include the record
     * being iterated over, and optionally the index. ie: apply(function($el, $ix) { ... })
     */
    public function apply(Closure $f): self
    {
        $countColumn = $this->countColumns();

        foreach ($this as $i => $record) {
            try {
                $newRecord = $countColumn !== 1 ? $f($record, $i) : $f($record[key($record)], $i);

                if ($newRecord === $record) {
                    throw new NotModifiedRecord; // can also be throw before from closure
                }

                if ($countColumn !== 1) {
                    $this->data->setRecord($i, $this->convertRecordToAbstract($newRecord));
                } else {
                    $this->data->setRecordColumn($i, $this->getColumnKey(key($record)), $newRecord);
                }
            } catch (NotModifiedRecord) {
            }
        }

        return $this;
    }

    /**
     * Sort the rows by its values
     */
    public function sortValues(array|string $by, bool $ascending = true): self
    {
        if (!$this->data instanceof SortableDriverInterface) {
            throw new SortNotSupportedByDriverException;
        }

        if (!\is_array($by)) {
            $by = [$by];
        }

        $this->data->usort(function (array $row_a, array $row_b) use ($by, $ascending): int {
            foreach ($by as $col) {
                $col = $this->getColumnKey($col);

                if ($row_a[$col] > $row_b[$col]) {
                    return $ascending ? 1 : -1;
                } elseif ($row_a[$col] < $row_b[$col]) {
                    return $ascending ? -1 : 1;
                }
            }

            return 0;
        });

        return $this;
    }

}
