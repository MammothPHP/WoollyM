<?php

declare(strict_types=1);

use CondorcetPHP\Oliphant\DataDrivers\DataDriverInterface;
use CondorcetPHP\Oliphant\DataDrivers\DriversExceptions\{InvalidDriverClassException, SortNotSupportedByDriverException};
use CondorcetPHP\Oliphant\DataFrame;

class InvalidDriverClass {}
class NotSortableDriver implements DataDriverInterface
{
    public function getRecordKey(int $recordKey): array
    {
        return [];
    }

    // public function getRecordColumn(int $recordKey, int $columnKey): mixed;

    public function setRecord(int $recordKey, array $recordData): void {}

    public function setRecordColumn(int $recordKey, int $columnKey, mixed $colValue): void {}

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

it('require a valid driver class', function (): void {
    new DataFrame(dataDriver: InvalidDriverClass::class);
})->throws(InvalidDriverClassException::class);

test('sort require a compatible driver', function (): void {
    $df = new DataFrame(dataDriver: NotSortableDriver::class);

    $df->sortValues('col1');
})->throws(SortNotSupportedByDriverException::class);
