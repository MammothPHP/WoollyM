<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Iterator;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException};
use MammothPHP\WoollyM\DataDrivers\{ColumnKeyType, DataDriver, WritableDriver};
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\{DriverIsNotWritableException, InvalidDriverClassException, KeyNotExistException};
use MammothPHP\WoollyM\DataDrivers\PhpArray\PhpArrayDriver;
use MammothPHP\WoollyM\Statements\Insert\Insert;
use MammothPHP\WoollyM\Statements\Select\ColumnRepresentation;
use WeakMap;

abstract class DataFramePrimitives
{
    public static string $defaultDataDriverClass = PhpArrayDriver::class;

    /* *****************************************************************************************************************
     *********************************************** Core Implementation ***********************************************
     ******************************************************************************************************************/

    protected DataDriver $data;
    public readonly bool $driverColumnModeText;

    protected array $columnIndexes = [];
    protected readonly WeakMap $columnRepresentations;

    protected ?Iterator $driverIterator;

    protected ?array $columnNamesCache = null;
    protected ?array $forcedTypesCache = null;

    abstract public function insert(): Insert; // Prevent IDE error

    /**
     * @param array<int,array> $data Array data to ingest
     * @param $dataDriver - Class of custom driver to use. if null, the PhpArray (in-memory) driver will used.
     * @throws InvalidDriverClassException
     */
    public function __construct(array $data = [], ?DataDriver $dataDriver = null)
    {
        $dataDriver ??= new self::$defaultDataDriverClass;

        $this->data = $dataDriver;
        $this->driverColumnModeText = $this->data::COLUMN_KEY_TYPE === ColumnKeyType::COLUMN_NAME;

        $this->columnRepresentations = new WeakMap;

        if (!empty($data)) {
            $this->mustBeWritableDriver();
            $this->insert()->append($data);
        }
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

    protected function mustBeWritableDriver(): void
    {
        if (!($this->data instanceof WritableDriver)) {
            throw new DriverIsNotWritableException;
        }
    }

    /* *****************************************************************************************************************
     ********************************************* Columns *************************************************************
     ******************************************************************************************************************/

    /* ******************************************* Public Column API **************************************************/

    /**
     * Adds a new column to the DataFrame. If column already exist, then nothing will happen.
     */
    public function addColumn(string $columnName): static
    {
        $this->mustBeWritableDriver();

        if (!$this->hasColumn($columnName)) {
            $this->columnIndexes[] = $newColumnIndex = new ColumnIndex($columnName, $this);
            $this->createColumnRepresentation($newColumnIndex);
            $this->clearColumnsCache();
        }

        return $this;
    }

    /**
     * Adds multiple columns to the DataFrame.
     * @param string[] $columnNames
     */
    public function addColumns(array $columnNames): static
    {
        foreach ($columnNames as $columnName) {
            $this->addColumn($columnName);
        }

        return $this;
    }

    /**
     * Removes a column (and all associated data) from the DataFrame.
     */
    public function removeColumn(string $columnName): static
    {
        $this->mustBeWritableDriver();
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

    /**
     * Returns a boolean of whether the specified column exists.
     */
    public function hasColumn(ColumnRepresentation|string $column): bool
    {
        if (array_search($column, $this->columns()) === false) {
            return false;
        }

        return true;
    }

    /**
     * Count unique columns already known
     */
    public function countColumns(): int
    {
        return \count($this->columnIndexes);
    }

    /**
     * Return ColumnRepresentation Object extending Select object
     * @return array<int,ColumnRepresentation>
     */
    public function columns(): array
    {
        $r = [];

        foreach ($this->columnIndexes as $key => $columnIndex) {
            $r[$key] = $this->columnRepresentations[$columnIndex];
        }

        return $r;
    }

    /**
     * Get unique columns already known
     * @return array<int,string>
     */
    public function columnsNames(): array
    {
        if ($this->columnNamesCache === null) {
            $this->columnNamesCache = array_map(fn(ColumnIndex $col): string => $col->getName(), $this->columnIndexes);
        }

        return $this->columnNamesCache;
    }

    /**
     * Assertion that the DataFrame must have the column specified. If not then an exception is thrown.
     *
     * @throws InvalidSelectException
     */
    public function mustHaveColumn(string $columnName): static
    {
        if ($this->hasColumn($columnName) === false) {
            throw new InvalidSelectException("{$columnName} doesn't exist in DataFrame");
        }

        return $this;
    }


    /* ******************************************* Internal Column API ************************************************/

    protected function getColumnKey(string $columnName): int
    {
        foreach ($this->columnIndexes as $columnKey => $column) {
            if ($column->getName() === $columnName) {
                return $columnKey;
            }
        }

        throw new InvalidSelectException;
    }

    /**
     * @internal
     */
    public function getColumnIndexObject(string $columnName): ColumnIndex
    {
        return $this->columnIndexes[$this->getColumnKey($columnName)];
    }

    protected function createColumnRepresentation(ColumnIndex $columnIndex): void
    {
        $this->columnRepresentations[$columnIndex] = new ColumnRepresentation($columnIndex);
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

    /* *****************************************************************************************************************
     ********************************************* Records *************************************************************
     ******************************************************************************************************************/

    /* ******************************************* Public Records API *************************************************/

    /**
     * Add a record, providing an array indexed by column => value
     * @param array<string, mixed> $recordArray
     */
    public function addRecord(array $recordArray): static
    {
        $this->mustBeWritableDriver();
        $this->data->addRecord($this->convertRecordToAbstract($recordArray));

        return $this;
    }

    /**
     * Update a record by record key. If key does not exist, record will be created.
     */
    public function updateRecord(int $key, array $recordArray): static
    {
        $this->mustBeWritableDriver();

        $this->data->setRecord($key, $this->convertRecordToAbstract($recordArray));

        return $this;
    }

    public function updateCell(int $recordKey, string $column, mixed $newValue): static
    {
        $this->mustBeWritableDriver();

        $this->data->setRecordColumn($recordKey, $this->getColumnKey($column), $newValue);

        return $this;
    }

    /**
     * Remove a record by key
     * @throws KeyNotExistException
     */
    public function deleteRecord(int $key): static
    {
        $this->mustBeWritableDriver();

        $this->data->deleteRecord($key);

        return $this;
    }

    /**
     * Get a record by key
     */
    public function getRecord(int $recordKey): Record
    {
        return $this->convertAbstractToRecordObject($this->data->getRecordKey($recordKey), $recordKey);
    }

    /**
     * Get a record by key and return an array
     * @return array<string,array>
     */
    public function getRecordAsArray(int $key): array
    {
        return $this->getRecord($key)->toArray();
    }

    /**
     * Check if a record key exist
     */
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

            $newRecord[$this->driverColumnModeText ? $recordKey : $columnKey] = $recordValue;
        }

        // ksort($newRow, \SORT_NUMERIC); // Degrade Performances

        return $newRecord;
    }

    /* ******************************************* Internal Records API ***********************************************/

    protected function convertAbstractToRecordObject(array $abstractRecord, int $recordKey): Record
    {
        $r = [];

        foreach ($this->columnsNames() as $ck => $cn) {
            $columnKey = $this->driverColumnModeText ? $cn : $ck;

            if (\array_key_exists($columnKey, $abstractRecord)) {
                $r[$cn] = $abstractRecord[$columnKey];
            }
        }

        return new Record($this, $recordKey, $r);
    }

    /* *****************************************************************************************************************
     ********************************************* Type System *********************************************************
     ******************************************************************************************************************/

    /**
     *@internal
     */
    public function getForcedTypesCache(): array
    {
        if ($this->forcedTypesCache === null) {
            $this->forcedTypesCache = array_map(fn(ColumnIndex $col): ?DataType => $col->getForcedType(), $this->columnIndexes);
        }

        return $this->forcedTypesCache;
    }
}
