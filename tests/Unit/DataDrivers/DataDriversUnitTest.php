<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataDrivers\DataDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\SortNotSupportedByDriverException;
use MammothPHP\WoollyM\DataFrame;

class InvalidDriverClass {}
class NotSortableDriver implements DataDriverInterface
{
    public function getRecordKey(int $recordKey): array
    {
        return [];
    }

    // public function getRecordColumn(int $recordKey, int $columnKey): mixed;

    public function setRecord(int $recordKey, array $recordData): void {}

    public function setRecordColumn(int $recordKey, int|string $columnKey, mixed $colValue): void {}

    public function addRecord(array $recordData): void {}

    public function removeRecord(int $recordKey): void {}

    public function keyExist(int $recordKey): bool
    {
        return true;
    }

    public function count(): int
    {
        return 42;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator([]);
    }
}

test('sort require a compatible driver', function (): void {
    $df = new DataFrame(dataDriver: new NotSortableDriver);

    $df->orderBy('col1');
})->throws(SortNotSupportedByDriverException::class);
