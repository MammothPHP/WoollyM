<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers\PhpArray;

use ArrayIterator;
use Closure;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\DataDrivers\{DataDriverInterface, SortableDriverInterface};

class PhpArrayDriver implements DataDriverInterface, SortableDriverInterface
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

    public function setRecordColumn(int $recordKey, int $columnKey, mixed $colValue): void
    {
        if (!$this->keyExist($recordKey)) {
            $this->setRecord($recordKey, []);
        }

        $this->data[$recordKey][$columnKey] = $colValue;
    }

    public function addRecord(array $recordData): void
    {
        $this->data[] = $recordData;
    }

    public function removeRecord(int $recordKey): void
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

    public function usort(Closure $callback): void
    {
        usort($this->data, $callback);
    }
}
