<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Iterator;
use MammothPHP\WoollyM\Exceptions\InvalidSelectException;
use MammothPHP\WoollyM\DataDrivers\DataDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\{InvalidDriverClassException, KeyNotExistException};
use MammothPHP\WoollyM\DataDrivers\PhpArray\PhpArrayDriver;
use MammothPHP\WoollyM\Statements\ColumnRepresentation;
use WeakMap;

abstract class DataFramePrimitives
{
    public static string $defaultDataDriverClass = PhpArrayDriver::class;

    /* *****************************************************************************************************************
     *********************************************** Core Implementation ***********************************************
     ******************************************************************************************************************/

    public bool $fillInNonExistentsCol = false;

    protected DataDriverInterface $data;
    protected array $columnIndexes = [];
    protected readonly WeakMap $columnRepresentations;

    protected ?Iterator $driverIterator;

    protected ?array $columnNamesCache = null;
    protected ?array $forcedTypesCache = null;

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
            $index = new ColumnIndex($index->getName(), $this);
            $this->createColumnRepresentation($index);
        }

        // Driver Iterator
        $this->driverIterator = null;
    }

    protected function initDriverIterator(): void
    {
        $this->driverIterator = $this->data->getIterator();
    }


    public function recordKeyExist(int $recordKey): bool
    {
        return $this->data->keyExist($recordKey);
    }

    protected function convertRecordToAbstract(array $recordArray): array
    {
        $newRecord = [];
        $columns = $this->columnsNames();
        $forcedTypes = $this->getForcedTypesCache();

        foreach ($recordArray as $recordKey => $recordValue) {
            if (!\in_array($recordKey, $columns, true)) {
                $this->addColumn($recordKey);
                $forcedTypes = $this->getForcedTypesCache();
            }

            $columnKey = $this->getColumnKey($recordKey);

            if ($type = $forcedTypes[$columnKey]) {
                $recordValue = $type->convert($recordValue);
            }

            $newRecord[$columnKey] = $recordValue;
        }

        // ksort($newRow, \SORT_NUMERIC); // Degrade Performances

        return $newRecord;
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
        if ($this->columnNamesCache === null) {
            $this->columnNamesCache = array_map(fn(ColumnIndex $col): string => $col->getName(), $this->columnIndexes);
        }

        return $this->columnNamesCache;
    }

    /*
     *@internal
     */
    public function getForcedTypesCache(): array
    {
        if ($this->forcedTypesCache === null) {
            $this->forcedTypesCache = array_map(fn(ColumnIndex $col): ?DataType => $col->getForcedType(), $this->columnIndexes);
        }

        return $this->forcedTypesCache;
    }

    protected function getColumnKey(string $columnName): int
    {
        foreach ($this->columnIndexes as $columnKey => $column) {
            if ($column->getName() === $columnName) {
                return $columnKey;
            }
        }

        throw new InvalidSelectException;
    }

    # Internal only
    public function getColumnIndexObject(string $columnName): ColumnIndex
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

        foreach ($this->columnsNames() as $ck => $cn) {
            $keyExist = \array_key_exists($ck, $abstractRecord);

            if ($this->fillInNonExistentsCol && !$keyExist) {
                $r[$cn] = null;
            } elseif ($keyExist) {
                $r[$cn] = $abstractRecord[$ck];
            }
        }

        return $r;
    }



    /**
     * Assertion that the DataFrame must have the column specified. If not then an exception is thrown.
     *
     * @throws InvalidSelectException
     */
    public function mustHaveColumn(string $columnName): self
    {
        if ($this->hasColumn($columnName) === false) {
            throw new InvalidSelectException("{$columnName} doesn't exist in DataFrame");
        }

        return $this;
    }

    /**
     * Returns a boolean of whether the specified column exists.
     *
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
     */
    public function addColumn(string $columnName): self
    {
        if (!$this->hasColumn($columnName)) {
            $this->columnIndexes[] = $newColumnIndex = new ColumnIndex($columnName, $this);
            $this->createColumnRepresentation($newColumnIndex);
            $this->clearColumnsCache();
        }

        return $this;
    }

    /**
     * Reset column cache
     *
     * @internal
     */
    public function clearColumnsCache(): void
    {
        $this->columnNamesCache = null;
        $this->forcedTypesCache = null;
    }

    /**
     * Adds multiple columns to the DataFrame.
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
     */
    public function removeColumn(string $columnName): self
    {
        $this->mustHaveColumn($columnName);

        $deletedKey = array_search(
            needle: $columnName,
            haystack: $this->columnsNames(),
            strict: true
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


}
