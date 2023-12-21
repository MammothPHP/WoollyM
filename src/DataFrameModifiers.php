<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use ArrayAccess;
use Closure;
use Exception;
use MammothPHP\WoollyM\DataDrivers\SortableDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\SortNotSupportedByDriverException;
use MammothPHP\WoollyM\Exceptions\NotModifiedRecord;
use Traversable;

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
     ******************************************* Modifiers *************************************************************
     ******************************************************************************************************************/

    /**
     * Allows user to "array_merge" two DataFrames so that the rows of one are appended to the rows of current DataFrame object
     * @param $iterable - The one to add to the current.
     */
    public function append(array|Traversable $iterable): self
    {
        foreach ($iterable as $dfRow) {
            $this->addRecord($dfRow);
        }

        return $this;
    }

    public function setColumn(string $targetColumn, mixed $rightHandSide): self
    {
        $this->addColumn($targetColumn);
        $this->mustHaveColumn($targetColumn);

        $this->col($targetColumn)->set($rightHandSide);

        return $this;
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
            } catch (NotModifiedRecord) {}
        }

        return $this;
    }

    /**
     * Replaces all occurences within the DataFrame of regex $pattern with string $replacement
     */
    public function preg_replace(array|string $pattern, array|string $replacement): self
    {
        return $this->apply(static function (array $record) use ($pattern, $replacement) {

            $totalReplacement = 0;

            foreach ($record as &$v) {
                $count = 0;

                $replaced = preg_replace(pattern: $pattern, replacement: $replacement, subject: (string) $v, count: $count);

                $v = $count > 0 ? $replaced : $v;
                $totalReplacement += $count;
            }

            if ($totalReplacement === 0) {
                throw new NotModifiedRecord;
            }

            return $record;
        });
    }

    /**
     * Remove record if closure return false
     * @param $f - ex: fn(array recordData, int $recordKey): bool => ...
     */
    public function filter(Closure $f): self
    {
        foreach ($this as $recordKey => $recordData) {
            if ($f($recordData, $recordKey) === false) {
                $this->removeRecord($recordKey);
            }
        }

        return $this;
    }

    /**
     * Apply new values to specific rows of the DataFrame using row index.
     *
     * If column is supplied, will apply to column.
     * If column is absent, will apply to row.
     *
     * By column:
     *      $df->applyIndexMap([
     *          2 => 'foo',
     *          3 => function($old_value) { return $new_value; },
     *          5 => 'baz',
     *      ], 'a');
     *
     * By row:
     *      $df->applyIndexMap([
     *          2 => function($old_row) { return $new_row; },
     *          3 => [ 'a' => 1, 'b' => 2, 'c' => 3 ],
     *      ]);
     */
    public function applyIndexMap(array|ArrayAccess $map, ?string $column = null): self
    {
        return $this->apply(static function (array $record, int $recordKey) use ($map, $column): array {
            if (isset($map[$recordKey])) {
                $value = $map[$recordKey];

                if (\is_callable($value) && $column === null) {
                    $record = $value($record);
                } elseif (\is_callable($value) && $column !== null) {
                    $record[$column] = $value($record[$column]);
                } elseif (\is_array($value) && $column === null) {
                    $record = $value;
                } elseif ((\is_string($value) || is_numeric($value) || \is_bool($value)) && $column !== null) {
                    $record[$column] = $value;
                }
            }

            return $record;
        });
    }

    /**
     * Allows user to apply type default values to certain columns when necessary. This is usually utilized
     * in conjunction with a database to avoid certain invalid type defaults (ie: dates of 0000-00-00).
     *
     * ie:
     *      $df->mapTypes([
     *          'some_amount' => 'DECIMAL',
     *          'some_int'    => 'INT',
     *          'some_date'   => 'DATE'
     *      ], ['Y-m-d'], 'm/d/Y');
     *
     * @throws Exception
     */
    public function convertTypes(array $typeMap, array|string|null $fromDateFormat = null, ?string $toDateFormat = null): self
    {
        foreach ($typeMap as $column => $type) {
            $this->col($column)->type(
                type: $type,
                fromDateFormat: $fromDateFormat,
                toDateFormat: $toDateFormat
            );
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
