<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataDrivers\DataDriver;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\{DriverIsNotWritableException, SortNotSupportedByDriverException};
use MammothPHP\WoollyM\DataFrame;

class InvalidDriverClass {}
class NotSortableAndNotWritableDriver implements DataDriver
{
    public function getRecordKey(int $recordKey): array
    {
        return [];
    }

    // public function getRecordColumn(int $recordKey, int $columnKey): mixed;

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
    $df = new DataFrame(dataDriver: new NotSortableAndNotWritableDriver);

    $df->orderBy('col1');
})->throws(SortNotSupportedByDriverException::class);


it('require a writable driver', function (): void {
    $df = new DataFrame(dataDriver: new NotSortableAndNotWritableDriver);

    expect(fn() => $df->addRecord(['a' => 1]))
        ->toThrow(DriverIsNotWritableException::class);
    expect(fn() => $df->updateRecord(0, []))
        ->toThrow(DriverIsNotWritableException::class);
    expect(fn() => $df->deleteRecord(0))
        ->toThrow(DriverIsNotWritableException::class);
    expect(fn() => $df->insert())
        ->toThrow(DriverIsNotWritableException::class);
    expect(fn() => $df->update())
        ->toThrow(DriverIsNotWritableException::class);
    expect(fn() => $df->delete())
        ->toThrow(DriverIsNotWritableException::class);
    expect(fn() => $df->addColumn('col2'))
        ->toThrow(DriverIsNotWritableException::class);
    expect(fn() => $df->removeColumn('col2'))
        ->toThrow(DriverIsNotWritableException::class);
});
