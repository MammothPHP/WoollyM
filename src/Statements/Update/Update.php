<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Update;

use ArrayAccess;
use Closure;
use MammothPHP\WoollyM\Exceptions\NotModifiedRecord;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\{SelectAllMode, Statement};

class Update extends Statement
{
    use SelectAllMode;

    /**
     * Update a record by key. Replace it totally by the new. If key does not exist, record will be created.
     */
    public function record(int $key, array $recordArray): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        return $df->updateRecord($key, $recordArray);
    }

    /**
     * Update a record by key, addin or replace provided column, untouching others.
     */
    public function mergeRecord(int $key, array $mergeValue): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        $mergeValue = array_merge($df->getRecordAsArray($key), $mergeValue);

        return $df->updateRecord($key, $mergeValue);
    }

    /**
     * Replaces all occurences within the DataFrame of regex $pattern with string $replacement
     */
    public function preg_replace(array|string $pattern, array|string $replacement): DataFrame
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
     * Applies a user-defined function to each record of the DataFrame. The parameters of the function include the record
     * being iterated over, and optionally the index. ie: apply(function($el, $ix) { ... })
     */
    public function apply(Closure $f): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        $countColumn = $df->countColumns();

        foreach ($this as $i => $record) {
            try {
                $newRecord = $countColumn !== 1 ? $f($record, $i) : $f($record[key($record)], $i);

                if ($newRecord === $record) {
                    throw new NotModifiedRecord; // can also be throw before from closure
                }

                if ($countColumn !== 1) {
                    $df->updateRecord($i, $newRecord);
                } else {
                    $df->updateCell($i, key($record), $newRecord);
                }
            } catch (NotModifiedRecord) {
            }
        }

        return $df;
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
    public function applyIndexMap(array|ArrayAccess $map, ?string $column = null): DataFrame
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
     */
    public function convertTypes(array $typeMap, array|string|null $fromDateFormat = null, ?string $toDateFormat = null): DataFrame
    {
        foreach ($typeMap as $column => $type) {
            $this->getLinkedDataFrame()->col($column)->type(
                type: $type,
                fromDateFormat: $fromDateFormat,
                toDateFormat: $toDateFormat
            );
        }

        return $this->getLinkedDataFrame();
    }

}
