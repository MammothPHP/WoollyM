<?php

declare(strict_types=1);

/**
 * Contains the DataFrameCore class.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */

namespace CondorcetPHP\Oliphant;

use CondorcetPHP\Oliphant\Exceptions\{DataFrameException, InvalidColumnException};
use Closure;
use Countable;
use DateTime;
use Exception;
use Iterator;
use ArrayAccess;
use PDO;
use RuntimeException;

/**
 * The DataFrameCore class acts as the implementation for the various data manipulation features of the DataFrame class.
 * @package   CondorcetPHP\Oliphant
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */
abstract class DataFrameCore implements ArrayAccess, Countable, Iterator
{
    /* *****************************************************************************************************************
     *********************************************** Core Implementation ***********************************************
     ******************************************************************************************************************/

    protected array $data = [];
    protected array $columns = [];

    public function __construct(array $data = [])
    {
        $this->addEntries($data);
    }

    public function addEntry(array $entry): self
    {
        if (\count($entry) > 0) {

            foreach(array_keys($entry) as $entryOneKey) {
                $this->addColumn($entryOneKey);
            }

            $this->data[] = $entry;
        }

        return $this;
    }

    public function addEntries(array $entries): self
    {
        foreach ($entries as $oneEntry) {
            $this->addEntry($oneEntry);
        }

        return $this;
    }

    /**
     * Returns the DataFrame's columns as an array.
     * @return array
     * @since  0.1.0
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * Returns a specific row index of the DataFrame.
     * @param  $index
     * @return array
     * @since  0.1.0
     */
    public function getIndex(int $index): array
    {
        return $this->data[$index];
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
        if (\count($this->columns()) > 1) {
            foreach ($this->data as $i => &$row) {
                $row = $f($row, $i);
            }
        }

        if (\count($this->columns()) === 1) {
            foreach ($this->data as $i => &$row) {
                $row[key($row)] = $f($row[key($row)], $i);
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
        return DataFrame::fromArray(array_filter($this->data, $f, \ARRAY_FILTER_USE_BOTH));
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
            $sqlColumns = implode(', ', $this->columns);
            // @codeCoverageIgnoreStart
        } elseif ($driver === 'mysql') {
            $sqlColumns = implode(' VARCHAR(255), ', $this->columns) . ' VARCHAR(255)';
        } else {
            throw new DataFrameException("{$driver} is not yet supported for DataFrame query.");
            // @codeCoverageIgnoreEnd
        }

        $pdo->exec('DROP TABLE IF EXISTS dataframe;');
        $pdo->exec("CREATE TABLE IF NOT EXISTS dataframe ({$sqlColumns});");

        $df = DataFrame::fromArray($this->data);
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
    public function hasColumn(Column|string $column): bool
    {
        if (array_search($column, $this->columns) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Adds a new column to the DataFrame.
     *
     * @internal
     * @param $columnName
     * @since 0.1.0
     */
    private function addColumn(string $column): self
    {
        if (!$this->hasColumn($column)) {
            $this->columns[] = new Column($column);
        }

        return $this;
    }

    /**
     * Adds multiple columns to the DataFrame.
     *
     * @internal
     * @param array $columnNames
     * @since 1.0.1
     */
    private function addColumns(array $columnNames): self
    {
        foreach ($columnNames as $columnName) {
            $this->addColumn($columnName);
        }

        return $this;
    }

    /**
     * Renames specific column.
     *
     * ie:
     *      $df->renameColumn('old_name', 'new_name');
     *
     * @param $from
     * @param $to
     */
    public function renameColumn(string $from, string $to): self
    {
        $this->mustHaveColumn($from);

        foreach ($this as $i => $row) {
            $keys = array_keys($row);
            $index = array_search($from, $keys, true);
            $keys[$index] = $to;
            $this->data[$i] = array_combine($keys, $row);
        }

        $key = array_search($from, $this->columns);

        if (($key) !== false) {
            $this->columns[$key]->name = $to;;
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

        foreach ($this as $i => $row) {
            unset($this->data[$i][$columnName]);
        }

        if (($key = array_search($columnName, $this->columns)) !== false) {
            unset($this->columns[$key]);
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
        foreach ($df as $dfEntry) {
            $this->addEntry($dfEntry);
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
    public function convertTypes(array $typeMap, array|string|null $fromDateFormat = null, ?string $toDateFormat = null): void
    {
        foreach ($this as $i => $row) {
            foreach ($typeMap as $column => $type) {
                $this->data[$i][$column] = match($type) {
                    DataType::NUMERIC => $this->convertNumeric($row[$column]),
                    DataType::INTEGER => $this->convertInt($row[$column]),
                    DataType::DATETIME => $this->convertDatetime($row[$column], $fromDateFormat, $toDateFormat),
                    DataType::CURRENCY => $this->convertCurrency($row[$column]),
                    DataType::ACCOUNTING => $this->convertAccounting($row[$column]),
                };
            }
        }
    }

    private function convertNumeric(mixed $value): int|float
    {
        if (is_numeric($value)) {
            return $value;
        }

        $value = str_replace(['$', ',', ' '], '', $value);

        if (substr($value, -1) == '-') {
            $value = '-'.substr($value, 0, -1);
        }

        $value = \floatval($value);

        return (\is_int($value / 1)) ? \intval($value) : $value;
    }

    private function convertInt(mixed $value): int
    {
        if (empty($value)) {
            return 0;
        }

        $value = (string) $value;

        if (substr($value, -1) === '-') {
            $value = '-'.substr($value, 0, -1);
        }

        $value = str_replace(['$', ',', ' '], '', $value);

        return \intval(str_replace(',', '', $value));
    }

    private function convertDatetime(mixed $value, array|string|null $fromFormat, string $toFormat): string
    {
        if (empty($value)) {
            return DateTime::createFromFormat('Y-m-d', '0001-01-01')->format($toFormat);
        }

        if (!\is_array($fromFormat)) {
            $fromFormat = [$fromFormat];
        }

        $dateFormatSnapshot = null;

        foreach ($fromFormat as $dateFormat) {
            $dateFormatSnapshot = $dateFormat;

            $oldDateTime = DateTime::createFromFormat($dateFormat, $value);
            if ($oldDateTime === false) {
                continue;
            } else {
                return $oldDateTime->format($toFormat);
            }
        }

        throw new RuntimeException("Error parsing date string '{$value}' with date format {$dateFormatSnapshot}");
    }

    private function convertCurrency(string $value): string
    {
        $value = explode('.', $value);
        $value[1] = $value[1] ?? '00';
        $value[0] = ($value[0] == '' || $value[0] == '-') ? '0' : $value[0];
        $value[1] = ($value[1] == '' || $value[1] == '0') ? '00' : $value[1];

        $value[0] = \floatval($value[0]);
        $dollars = number_format($value[0]).'.'.$value[1];

        if (substr($dollars, 0, 1) == '-') {
            $dollars = '-$' . ltrim($dollars, '-');
        } elseif (substr($dollars, -1) == '-') {
            $dollars = '-$' . rtrim($dollars, '-');
        } else {
            $dollars = '$'.$dollars;
        }

        return $dollars;
    }

    private function convertAccounting(string $value): string
    {
        $value = explode('.', $value);
        $value[1] = $value[1] ?? '00';
        $value[0] = ($value[0] == '' || $value[0] == '-') ? '0' : $value[0];
        $value[1] = ($value[1] == '' || $value[1] == '0') ? '00' : $value[1];

        $value[0] = \floatval($value[0]);
        $dollars = number_format($value[0]) . '.' . $value[1];

        if (substr($dollars, 0, 1) == '-') {
            $dollars = '('.ltrim($dollars, '-').')';
        } elseif (substr($dollars, -1) == '-') {
            $dollars = '('.rtrim($dollars, '-').')';
        }

        return '$'.$dollars;
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
        foreach ($this->data as $row) {
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
     * @param $by string|string[] Columns to sort the values by
     * @param $ascending bool Sort the values ascending (or if `false` descending)
     * @return void
     */
    public function sortValues(array|string $by, bool $ascending = true): void
    {
        if (!\is_array($by)) {
            $by = [$by];
        }

        usort($this->data, static function (array $row_a, array $row_b) use ($by, $ascending): int {
            foreach ($by as $col) {
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
    public function offsetExists(mixed $columnName): bool
    {
        foreach ($this as $row) {
            if (!\array_key_exists($columnName, $row)) {
                return false;
            }
        }

        return true;
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
    public function offsetGet(mixed $columnName): mixed
    {
        $this->mustHaveColumn($columnName);

        $getColumn = static function ($el) use ($columnName) {
            return $el[$columnName];
        };

        $data = array_map($getColumn, $this->data);

        foreach ($data as &$row) {
            $row = [$columnName => $row];
        }

        return new DataFrame($data);
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
    public function offsetSet(mixed $targetColumn, mixed $rightHandSide): void
    {
        if ($rightHandSide instanceof DataFrame) {
            $this->offsetSetDataFrame($targetColumn, $rightHandSide);
        } elseif ($rightHandSide instanceof Closure) {
            $this->offsetSetClosure($targetColumn, $rightHandSide);
        } else {
            $this->offsetSetValue($targetColumn, $rightHandSide);
        }
    }

    /**
     * Allows user set DataFrame columns from a single-column DataFrame.
     *      ie:
     *          $df['bar'] = $df['foo'];
     *
     * @internal
     * @param  $targetColumn
     * @param  DataFrame $df
     * @throws DataFrameException
     * @since  0.1.0
     */
    private function offsetSetDataFrame(string $targetColumn, DataFrame $df): void
    {
        if (\count($df->columns()) !== 1) {
            $msg = 'Can only set a new column from a DataFrame with a single ';
            $msg .= 'column.';

            throw new DataFrameException($msg);
        }

        if (\count($df) != \count($this)) {
            $msg = 'Source and target DataFrames must have identical number ';
            $msg .= 'of rows.';

            throw new DataFrameException($msg);
        }

        $this->addColumn($targetColumn);

        foreach ($this as $i => $row) {
            $this->data[$i][$targetColumn] = current($df->getIndex($i));
        }
    }

    /**
     * Allows user set DataFrame columns from a Closure.
     *      ie:
     *          $df['foo'] = function ($foo) { return $foo + 1; };
     *
     * @internal
     * @param $targetColumn
     * @param Closure $f
     * @since 0.1.0
     */
    private function offsetSetClosure(string $targetColumn, Closure $f): void
    {
        foreach ($this as $i => $row) {
            $this->data[$i][$targetColumn] = $f($row[$targetColumn]);
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
     * @param $targetColumn
     * @param $value
     * @since 0.1.0
     */
    private function offsetSetValue(?string $targetColumn, mixed $value): void
    {
        if (!empty($targetColumn)) {
            $this->addColumn($targetColumn);
            foreach ($this as $i => $row) {
                $this->data[$i][$targetColumn] = $value;
            }
        } elseif (\is_array($value)) {
            $this->addEntry($value);
        }
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
        $this->removeColumn($offset);
    }

    /* *****************************************************************************************************************
     ********************************************* Iterator Implementation *********************************************
     ******************************************************************************************************************/

    private $pointer = 0;

    /**
     * Return the current element
     *
     * @link   http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since  0.1.0
     */
    public function current(): mixed
    {
        return $this->data[$this->key()];
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
        $this->pointer++;
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
        return $this->pointer;
    }

    /**
     * Checks if current position is valid
     *
     * @link   http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *                 Returns true on success or false on failure.
     * @since  0.1.0
     */
    public function valid(): bool
    {
        return isset($this->data[$this->key()]);
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
        $this->pointer = 0;
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
        return \count($this->data);
    }
}
