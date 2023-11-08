<?php

declare(strict_types=1);

/**
 * Contains the DataFrameCore class.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/MammothPHP\WoollyM/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/MammothPHP\WoollyM
 * @since     0.1.0
 */

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\{DataFrameException, InvalidColumnException};
use Closure;
use Countable;
use Exception;
use Iterator;
use ArrayAccess;
use MammothPHP\WoollyM\DataDrivers\{DataDriverInterface, SortableDriverInterface};
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\{InvalidDriverClassException, KeyNotExistException, SortNotSupportedByDriverException};
use MammothPHP\WoollyM\DataDrivers\PhpArray\PhpArrayDriver;
use PDO;
use WeakMap;

/**
 * The DataFrameCore class acts as the implementation for the various data manipulation features of the DataFrame class.
 * @package   MammothPHP\WoollyM
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/MammothPHP\WoollyM/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/MammothPHP\WoollyM
 * @since     0.1.0
 */
abstract class DataFrameCore implements ArrayAccess, Countable, Iterator
{
    public static string $defaultDataDriverClass = PhpArrayDriver::class;

    /* *****************************************************************************************************************
     *********************************************** Core Implementation ***********************************************
     ******************************************************************************************************************/

    protected DataDriverInterface $data;
    protected array $columnIndexes = [];
    protected WeakMap $columnRepresentations;

    public function __construct(array $data = [], ?string $dataDriver = null)
    {
        $dataDriver ??= self::$defaultDataDriverClass;

        if (!is_subclass_of($dataDriver, DataDriverInterface::class, true)) {
            throw new InvalidDriverClassException;
        }

        $this->data = new $dataDriver;
        $this->columnRepresentations = new WeakMap;

        $this->addRecords($data);
    }

    public function __clone()
    {
        // Data
        $this->data = clone $this->data;

        // Columns
        $this->columnRepresentations = new WeakMap;

        foreach ($this->columnIndexes as &$index) {
            $index = new ColumnIndex($index->name, $this);
            $this->createColumnRepresentation($index);
        }
    }


    public function recordKeyExist(int $recordKey): bool
    {
        return $this->data->keyExist($recordKey);
    }

    protected function convertRecordToAbstract(array $rowArray): array
    {
        $newRow = [];

        foreach ($rowArray as $rowKey => $rowValue) {
            $this->addColumn($rowKey);

            if ($type = $this->getColumnIndexObject($rowKey)->forcedType) {
                $rowValue = $type->convert($rowValue);
            }

            $newRow[$this->getColumnKey($rowKey)] = $rowValue;
        }

        ksort($newRow, \SORT_NUMERIC);

        return $newRow;
    }

    public function countColumns(): int
    {
        return \count($this->columnIndexes);
    }

    public function columns(): array
    {
        $r = [];

        foreach ($this->columnIndexes as $key => $columnIndex) {
            $r[$key] = $this->columnRepresentations[$columnIndex];
        }

        return $r;
    }

    public function columnsNames(): array
    {
        return array_map(fn(ColumnRepresentation $col): string => $col->getName(), $this->columns());
    }

    public function col(string $columnName): ColumnRepresentation
    {
        return $this->columnRepresentations[$this->getColumnIndexObject($columnName)];
    }

    public function column(string $columnName): ColumnRepresentation
    {
        return $this->col($columnName);
    }

    protected function getColumnKey(string $columnName): int
    {
        foreach ($this->columnIndexes as $columnKey => $column) {
            if ($column->name === $columnName) {
                return $columnKey;
            }
        }

        throw new InvalidColumnException;
    }

    protected function getColumnIndexObject(string $columnName): ColumnIndex
    {
        return $this->columnIndexes[$this->getColumnKey($columnName)];
    }

    public function getRecord(int $recordKey): array
    {
        return $this->convertAbstractRecordToArray($this->data->getRecordKey($recordKey));
    }

    public function addRecord(array $recordArray): self
    {
        $this->data->addRecord($this->convertRecordToAbstract($recordArray));

        return $this;
    }

    public function addRecords(array $records): self
    {
        foreach ($records as $oneRow) {
            $this->addRecord($oneRow);
        }

        return $this;
    }

    public function updateRecord(int $recordKey, mixed $recordArray): self
    {
        $this->data->setRecord($recordKey, $this->convertRecordToAbstract($recordArray));

        return $this;
    }

    public function removeRecord(int $recordKey): self
    {
        try {
            $this->data->removeRecord($recordKey);
        } catch (KeyNotExistException) {
        }

        return $this;
    }

    public function convertAbstractRecordToArray(array $abstractRecord): array
    {
        $r = [];

        foreach ($abstractRecord as $k => $v) {
            $r[$this->columnIndexes[$k]->name] = $v;
        }

        return $r;
    }

    /**
     * Applies a user-defined function to each row of the DataFrame. The parameters of the function include the row
     * being iterated over, and optionally the index. ie: apply(function($el, $ix) { ... })
     *
     * @param  Closure $f
     * @return DataFrameCore
     * @since  0.1.0
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
     * @param  array $map keys are row indices, values are static
     * @param  $column
     * @return DataFrameCore
     * @since  0.1.0
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
     * @param  Closure $f
     * @return DataFrame
     * @since  0.1.0
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
     * @param $sql
     * @param PDO $pdo
     * @return DataFrame
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
            $sqlColumns = implode(', ', $this->columnIndexes);
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
     * Assertion that the DataFrame must have the column specified. If not then an exception is thrown.
     *
     * @param  $columnName
     * @throws InvalidColumnException
     * @since  0.1.0
     */
    public function mustHaveColumn(string $columnName): self
    {
        if ($this->hasColumn($columnName) === false) {
            throw new InvalidColumnException("{$columnName} doesn't exist in DataFrame");
        }

        return $this;
    }

    /**
     * Returns a boolean of whether the specified column exists.
     *
     * @param  $columnName
     * @return bool
     * @since  0.1.0
     */
    public function hasColumn(ColumnRepresentation|string $column): bool
    {
        if (array_search($column, $this->columns()) === false) {
            return false;
        }

        return true;
    }

    public function createColumnRepresentation(ColumnIndex $columnIndex): void
    {
        $this->columnRepresentations[$columnIndex] = new ColumnRepresentation($columnIndex);
    }

    /**
     * Adds a new column to the DataFrame.
     *
     * @internal
     * @param $columnName
     * @since 0.1.0
     */
    public function addColumn(string $columnName): self
    {
        if (!$this->hasColumn($columnName)) {
            $this->columnIndexes[] = $newColumnIndex = new ColumnIndex($columnName, $this);
            $this->createColumnRepresentation($newColumnIndex);
        }

        return $this;
    }

    /**
     * Adds multiple columns to the DataFrame.
     *
     * @internal
     * @since 1.0.1
     */
    public function addColumns(array $columnNames): self
    {
        foreach ($columnNames as $columnName) {
            $this->addColumn($columnName);
        }

        return $this;
    }

    /**
     * Removes a column (and all associated data) from the DataFrame.
     *
     * @param $columnName
     * @since 0.1.0
     */
    public function removeColumn(string $columnName): self
    {
        $this->mustHaveColumn($columnName);

        $deletedKey = array_search(
            needle: $columnName,
            haystack: $this->columnIndexes,
            strict: false
        );

        if ($deletedKey !== false) {
            unset($this->columnIndexes[$deletedKey]);

            foreach ($this->data as $recordKey => $row) {
                $row = array_filter(
                    array: $row,
                    callback: fn(int $arrayKey): bool => $arrayKey !== $deletedKey,
                    mode: \ARRAY_FILTER_USE_KEY
                );

                $this->data->setRecord($recordKey, $row);
            }
        }

        return $this;
    }

    /**
     * Allows user to "array_merge" two DataFrames so that the rows of one are appended to the rows of another.
     *
     * @return $this
     */
    public function append(DataFrame $df): self
    {
        foreach ($df as $dfRow) {
            $this->addRecord($dfRow);
        }

        return $this;
    }

    /**
     * Replaces all occurences within the DataFrame of regex $pattern with string $replacement
     *
     * @param $pattern
     * @param $replacement
     * @return DataFrameCore
     */
    public function preg_replace($pattern, $replacement): self
    {
        return $this->apply(static function (array $row) use ($pattern, $replacement) {
            return preg_replace($pattern, $replacement, $row);
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
     * @param array $typeMap
     * @param null|string $fromDateFormat The date format of the input.
     * @param null|string $toDateFormat The date format of the output.
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
     * @param $columns
     * @return DataFrame
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
     * Outputs a DataFrame as a two-dimensional associative array.
     * @return array
     * @since 0.1.0
     */
    public function toArray(): array
    {
        $r = [];

        foreach ($this as $key => $row) {
            $r[$key] = $row;
        }

        return $r;
    }

    /**
     * Sort the rows by its values
     *
     * @param $by string|string[] Columns to sort the values by
     * @param $ascending bool Sort the values ascending (or if `false` descending)
     * @return void
     */
    public function sortValues(array|string $by, bool $ascending = true): void
    {
        if (! $this->data instanceof SortableDriverInterface) {
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
     ******************************************* ArrayAccess Implementation ********************************************
     ******************************************************************************************************************/

    /**
     * Provides isset($df['column']) functionality.
     *
     * @internal
     * @param  mixed $columnName
     * @return bool
     * @since  0.1.0
     */
    public function offsetExists(mixed $index): bool
    {
        return $this->recordKeyExist($index);
    }

    /**
     * Allows user retrieve DataFrame subsets from a two-dimensional array by
     * simply requesting an element of the instantiated DataFrame.
     *      ie: $fooDF = $df['foo'];
     *
     * @internal
     * @param  mixed $columnName
     * @return DataFrame
     * @throws InvalidColumnException
     * @since  0.1.0
     */
    public function offsetGet(mixed $index): mixed
    {
        return $this->data->getRecordKey($index);

        // $this->mustHaveColumn($columnName);

        // $data = [];

        // foreach ($this as $row) {
        //     $data[] = [$columnName => $row[$columnName]];
        // }

        // return new DataFrame($data);
    }

    public function setColumn(string $targetColumn, mixed $rightHandSide): self
    {
        $this->addColumn($targetColumn);
        $this->mustHaveColumn($targetColumn);

        $this->col($targetColumn)->set($rightHandSide);

        return $this;
    }

    /**
     * Allows user set DataFrame columns from a Closure, value, array, or another single-column DataFrame.
     *      ie:
     *          $df[$targetColumn] = $rightHandSide
     *          $df['bar'] = $df['foo'];
     *          $df['bar'] = $df->foo;
     *          $df['foo'] = function ($foo) { return $foo + 1; };
     *          $df['foo'] = 'bar';
     *          $df[] = [['gender'=>'Female','name'=>'Luy'],['title'=>'Mr','name'=>'Noah']];
     *
     * @internal
     * @param  mixed $targetColumn
     * @param  mixed $rightHandSide
     * @throws DataFrameException
     * @since  0.1.0
     */
    public function offsetSet(mixed $index, mixed $row): void
    {
        \is_int($index) ? $this->updateRecord($index, $row) : $this->addRecord($row);
    }

    /**
     * Allows user to remove columns from the DataFrame using unset.
     *      ie: unset($df['column'])
     *
     * @param  mixed $offset
     * @throws InvalidColumnException
     * @since  0.1.0
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeRecord($offset);
    }

    /* *****************************************************************************************************************
     ********************************************* Iterator Implementation *********************************************
     ******************************************************************************************************************/

    protected Iterator $driverIterator;

    protected function initDriverIterator(): void
    {
        $this->driverIterator = $this->data->getIterator();
    }

    /**
    * Return the current element
    *
    * @link   http://php.net/manual/en/iterator.current.php
    * @return mixed Can return any type.
    * @since  0.1.0
    */
    public function current(): mixed
    {
        return $this->getRecord($this->key());
    }

    /**
     * Move forward to next element
     *
     * @link   http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since  0.1.0
     */
    public function next(): void
    {
        $this->driverIterator->next();
    }

    /**
     * Return the key of the current element
     *
     * @link   http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since  0.1.0
     */
    public function key(): mixed
    {
        return $this->driverIterator->key();
    }

    /**
     * Checks if current recordKey is valid
     *
     * @link   http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *                 Returns true on success or false on failure.
     * @since  0.1.0
     */
    public function valid(): bool
    {
        return $this->driverIterator->valid();
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link   http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since  0.1.0
     */
    public function rewind(): void
    {
        $this->initDriverIterator();
    }

    /* *****************************************************************************************************************
     ******************************************** Countable Implementation *********************************************
     ******************************************************************************************************************/

    /**
     * Count elements of an object
     *
     * @link   http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *             The return value is cast to an integer.
     * @since  0.1.0
     */
    public function count(): int
    {
        return $this->data->count();
    }
}
