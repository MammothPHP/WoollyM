<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers\PhpArray;

use ArrayIterator;
use Closure;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\DataDrivers\{SortableDriver, WritableDriver};
use MammothPHP\WoollyM\Exceptions\DataFrameException;

/**
 * @internal
 */
class PhpArrayDriver implements SortableDriver, WritableDriver
{
    protected array $data = [];

    public function mustHaveValidRecordKey(int $recordKey): void
    {
        if (!\array_key_exists($recordKey, $this->data)) {
            throw new KeyNotExistException;
        }
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    public function getRecordKey(int $recordKey): array
    {
        $this->mustHaveValidRecordKey($recordKey);

        return $this->data[$recordKey];
    }

    public function setRecord(int $recordKey, array $recordData): void
    {
        $this->data[$recordKey] = $recordData;
    }

    public function setRecordColumn(int $recordKey, int|string $columnKey, mixed $colValue): void
    {
        if (\is_string($columnKey)) {
            throw new DataFrameException;
        }

        if (!$this->keyExist($recordKey)) {
            $this->setRecord($recordKey, [$columnKey => $colValue]);
        } else {
            $this->data[$recordKey][$columnKey] = $colValue;
        }
    }

    public function addRecord(array $recordData): void
    {
        $this->data[] = $recordData;
    }

    public function deleteRecord(int $recordKey): void
    {
        $this->mustHaveValidRecordKey($recordKey);

        unset($this->data[$recordKey]);
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function keyExist(int $recordKey): bool
    {
        return isset($this->data[$recordKey]);
    }

    public function uasort(Closure $callback): void
    {
        uasort($this->data, $callback);
    }
}
