<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use MammothPHP\WoollyM\DataDrivers\SortableDriver;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\SortNotSupportedByDriverException;
use MammothPHP\WoollyM\Sort\{Asc, Sort};
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
        $this->mustBeWritableDriver();

        return new Delete($this);
    }

    /**
     * Return an Update statement, methods will return the same DataFrame object
     */
    public function insert(): Insert
    {
        $this->mustBeWritableDriver();

        return new Insert($this);
    }

    /**
     * Return an Update statement, methods will return the same DataFrame object
     */
    public function update(): Update
    {
        $this->mustBeWritableDriver();

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
    public function orderBy(Sort|string ...$by): static
    {
        if (!$this->data instanceof SortableDriver) {
            throw new SortNotSupportedByDriverException;
        }

        $this->data->uasort(function (array $row_a, array $row_b) use ($by): int {
            foreach ($by as $sort) {
                if (\is_string($sort)) {
                    $sort = Asc::col($sort);
                }

                $col = $this->getColumnKey($sort->col);
                $ascending = $sort instanceof Asc;

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
