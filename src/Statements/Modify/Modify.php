<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Modify;

use ArrayAccess;
use Closure;
use MammothPHP\WoollyM\Exceptions\NotModifiedRecord;
use MammothPHP\WoollyM\{DataFrame, LinkedDataFrame};
use Traversable;

class Modify
{
    use LinkedDataFrame;

    public function __construct(DataFrame $df)
    {
        $this->setLinkedDataFrame($df);
    }

    /**
     * Allows user to "array_merge" two DataFrames so that the rows of one are appended to the rows of current DataFrame object
     * @param $iterable - The one to add to the current.
     */
    public function append(array|Traversable $iterable): DataFrame
    {
        foreach ($iterable as $dfRow) {
            $this->getLinkedDataFrame()->addRecord($dfRow);
        }

        return $this->getLinkedDataFrame();
    }

    /**
     * Replaces all occurences within the DataFrame of regex $pattern with string $replacement
     */
    public function preg_replace(array|string $pattern, array|string $replacement): DataFrame
    {
        return $this->getLinkedDataFrame()->apply(static function (array $record) use ($pattern, $replacement) {

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
    public function filter(Closure $f): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        foreach ($df as $recordKey => $recordData) {
            if ($f($recordData, $recordKey) === false) {
                $df->removeRecord($recordKey);
            }
        }

        return $this->getLinkedDataFrame();
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
        return $this->getLinkedDataFrame()->apply(static function (array $record, int $recordKey) use ($map, $column): array {
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

    public function setColumn(string $targetColumn, mixed $rightHandSide): self
    {
        $this->getLinkedDataFrame()->addColumn($targetColumn);
        $this->getLinkedDataFrame()->mustHaveColumn($targetColumn);

        $this->getLinkedDataFrame()->col($targetColumn)->set($rightHandSide);

        return $this;
    }

}
