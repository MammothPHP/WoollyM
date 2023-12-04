<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use Exception;
use MammothPHP\WoollyM\DataDrivers\SortableDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\SortNotSupportedByDriverException;

abstract class DataFrameModifiers extends DataFrameStatements
{
    /* *****************************************************************************************************************
     ******************************************* Copy ******************************************************************
     ******************************************************************************************************************/

    public function copy(): Copy
    {
        return new Copy($this);
    }

    /* *****************************************************************************************************************
     ******************************************* Modifiers *************************************************************
     ******************************************************************************************************************/

    /**
     * Allows user to "array_merge" two DataFrames so that the rows of one are appended to the rows of another.
     */
    public function append(DataFrame $df): self
    {
        foreach ($df as $dfRow) {
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

    public function sortColumns(?Closure $callback = null): self
    {
        $callback ??= fn(ColumnIndex $a, ColumnIndex $b): int => $a->getName() <=> $b->getName();

        uasort($this->columnIndexes, $callback);
        $this->clearColumnsCache();

        return $this;
    }

    /**
     * Applies a user-defined function to each row of the DataFrame. The parameters of the function include the row
     * being iterated over, and optionally the index. ie: apply(function($el, $ix) { ... })
     */
    public function apply(Closure $f): self
    {
        if ($this->countColumns() > 1) {
            foreach ($this as $i => $row) {
                $this->data->setRecord($i, $this->convertRecordToAbstract($f($row, $i)));
            }
        } elseif ($this->countColumns() === 1) {
            foreach ($this as $i => $row) {
                $this->data->setRecordColumn($i, $this->getColumnKey(key($row)), $f($row[key($row)], $i));
            }
        }

        return $this;
    }

    /**
     * Replaces all occurences within the DataFrame of regex $pattern with string $replacement
     */
    public function preg_replace(array|string $pattern, array|string $replacement): self
    {
        return $this->apply(static function (array $row) use ($pattern, $replacement) {
            return preg_replace($pattern, $replacement, $row);
        });
    }

    public function filter(Closure $f): self
    {
        foreach ($this as $recordKey => $recordArray) {
            if ($f($recordArray, $recordKey) === false) {
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
    public function applyIndexMap(array $map, ?string $column = null): self
    {
        return $this->apply(static function (array &$record, int $i) use ($map, $column): array {
            if (isset($map[$i])) {
                $value = $map[$i];

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
