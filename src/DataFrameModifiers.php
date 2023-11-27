<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\DataFrameException;
use Closure;
use Exception;
use MammothPHP\WoollyM\DataDrivers\SortableDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\SortNotSupportedByDriverException;
use MammothPHP\WoollyM\Statements\{ColumnRepresentation, Select, SelectAll};
use PDO;

abstract class DataFrameModifiers extends DataFrameAccessors
{
    /* *****************************************************************************************************************
     ******************************************* Modifiers ********************************************
     ******************************************************************************************************************/

    /**
     * Allows user to "array_merge" two DataFrames so that the rows of one are appended to the rows of another.
     *
     */
    public function append(DataFrame $df): self
    {
        foreach ($df as $dfRow) {
            $this->addRecord($dfRow);
        }

        return $this;
    }

    public function sortColumns(?Closure $callback = null): self
    {
        $callback ??= fn(ColumnIndex $a, ColumnIndex $b): int => $a->name <=> $b->name;

        uasort($this->columnIndexes, $callback);

        return $this;
    }

    /**
     * Applies a user-defined function to each row of the DataFrame. The parameters of the function include the row
     * being iterated over, and optionally the index. ie: apply(function($el, $ix) { ... })
     *
     */
    public function apply(Closure $f): self
    {
        if (\count($this->columnIndexes) > 1) {
            foreach ($this as $i => $row) {
                $this->data->setRecord($i, $this->convertRecordToAbstract($f($row, $i)));
            }
        } elseif (\count($this->columnIndexes) === 1) {
            foreach ($this as $i => $row) {
                $this->data->setRecordColumn($i, $this->getColumnKey(key($row)), $f($row[key($row)], $i));
            }
        }

        return $this;
    }

    /**
     * Replaces all occurences within the DataFrame of regex $pattern with string $replacement
     *
     */
    public function preg_replace($pattern, $replacement): self
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
     *
     */
    public function applyIndexMap(array $map, ?string $column = null)
    {
        return $this->apply(static function (&$row, $i) use ($map, $column) {
            if (isset($map[$i])) {
                $value = $map[$i];

                if (\is_callable($value) && $column === null) {
                    $row = $value($row);
                } elseif (\is_callable($value) && $column !== null) {
                    $row[$column] = $value($row[$column]);
                } elseif (\is_array($value) && $column === null) {
                    $row = $value;
                } elseif ((\is_string($value) || is_numeric($value) || \is_bool($value)) && $column !== null) {
                    $row[$column] = $value;
                }
            }

            return $row;
        });
    }

    /**
     * Filter DataFrame rows using user-defined function. The parameters of the function include the row
     * being iterated over, and the index.
     *
     * ie:
     *      $df = $df->array_filter(function($row, $index) { ... });
     *
     */
    public function array_filter(Closure $f): self
    {
        return DataFrame::fromArray(array_filter($this->toArray(), $f, \ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Allows SQL to be used to perform operations on the DataFrame
     *
     * Table name will always be 'dataframe'
     *
     * @throws DataFrameException
     */
    public function query(string $sql, ?PDO $pdo = null): self
    {
        $sql = trim($sql);
        $queryType = trim(strtoupper(strtok($sql, ' ')));

        if ($pdo === null) {
            $pdo = new PDO('sqlite::memory:');
        }

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $sqlColumns = implode(', ', $this->columnsNames());
        // @codeCoverageIgnoreStart
        } elseif ($driver === 'mysql') {
            $sqlColumns = implode(' VARCHAR(255), ', $this->columnIndexes) . ' VARCHAR(255)';
        } else {
            throw new DataFrameException("{$driver} is not yet supported for DataFrame query.");
            // @codeCoverageIgnoreEnd
        }

        $pdo->exec('DROP TABLE IF EXISTS dataframe;');
        $pdo->exec("CREATE TABLE IF NOT EXISTS dataframe ({$sqlColumns});");

        $df = DataFrame::fromArray($this->toArray());
        $df->toSQL('dataframe', $pdo);

        if ($queryType === 'SELECT') {
            $result = $pdo->query($sql);
        } else {
            $pdo->exec($sql);
            $result = $pdo->query('SELECT * FROM dataframe;');
        }

        $results = $result->fetchAll(PDO::FETCH_ASSOC);

        $pdo->exec('DROP TABLE IF EXISTS dataframe;');

        return DataFrame::fromArray($results);
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
     * Returns unique values of given column(s)
     *
     */
    public function unique(array|string $columns): self
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }

        $groupedData = [];
        $uniqueColumns = [];
        foreach ($this as $row) {
            $uniqueKey = null;
            foreach ($columns as $column) {
                $uniqueKey .= $row[$column];
            }

            if (isset($uniqueColumns[$uniqueKey])) {
                continue;
            } else {
                $uniqueColumns[$uniqueKey] = true;

                $new_row = [];
                foreach ($columns as $column) {
                    $new_row[$column] = $row[$column];
                }

                $groupedData[] = $new_row;
            }
        }

        return DataFrame::fromArray($groupedData);
    }

    /**
     * Sort the rows by its values
     *
     */
    public function sortValues(array|string $by, bool $ascending = true): void
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
    }

    /* *****************************************************************************************************************
     ******************************************* Statements ********************************************
     ******************************************************************************************************************/

    public function select(string ...$selections): Select
    {
        return new Select($this, ...$selections);
    }

    public function selectAll(): SelectAll
    {
        return new SelectAll($this);
    }

    public function col(string $columnName): ColumnRepresentation
    {
        return $this->columnRepresentations[$this->getColumnIndexObject($columnName)];
    }

    public function column(string $columnName): ColumnRepresentation
    {
        return $this->col($columnName);
    }
}
