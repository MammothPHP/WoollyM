<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use MammothPHP\WoollyM\DataDrivers\SortableDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\SortNotSupportedByDriverException;
use MammothPHP\WoollyM\Statements\Delete\Delete;
use MammothPHP\WoollyM\Statements\Insert\Insert;
use MammothPHP\WoollyM\Statements\Update\Update;

abstract class DataFrameModifiers extends DataFrameStatements
{
    /* *****************************************************************************************************************
     ******************************************* copy / insert / update / delete / sort API ****************************
     ******************************************************************************************************************/

    /**
     * Return a Copy object, methods will return new DataFrame objects.
     */
    public function extract(DataFrame $to = new DataFrame): Extract
    {
        return new Extract($this, $to);
    }

    /**
     * Return a Copy object, methods will return new DataFrame objects.
     */
    public function delete(): Delete
    {
        return new Delete($this);
    }

    /**
     * Return an Update statement, methods will return the same DataFrame object
     */
    public function insert(): Insert
    {
        return new Insert($this);
    }

    /**
     * Return an Update statement, methods will return the same DataFrame object
     */
    public function update(): Update
    {
        return new Update($this);
    }

    /* *****************************************************************************************************************
     ******************************************* Sorts *****************************************************************
     ******************************************************************************************************************/

    /**
     * Sort column order using a closure. Then retrieve records will respect the new order.
     * @param $callback - If null, sort will be alphabetic. For closure, example fn(string $a, string $b): int => $a <=> $b;
     */
    public function sortColumns(?Closure $callback = null): static
    {
        $callback ??= fn(string $a, string $b): int => $a <=> $b;
        $finalCallback = fn(ColumnIndex $a, ColumnIndex $b): int => $callback($a->getName(), $b->getName()); // protected ColumnIndex leak

        uasort($this->columnIndexes, $finalCallback);
        $this->clearColumnsCache();

        return $this;
    }

    /**
     * Sort the records by columns
     */
    public function sortRecordsByColumns(array|string $by, bool $ascending = true): static
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
