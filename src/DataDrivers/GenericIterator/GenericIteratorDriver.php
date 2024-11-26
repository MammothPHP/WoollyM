<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers\PhpArray;

use Iterator;
use MammothPHP\WoollyM\DataDrivers\DataDriver;

/**
 * @internal
 */
abstract class GenericIteratorDriver implements DataDriver
{
    abstract public function getIterator(): Iterator;

    abstract public function getRecordKey(int $recordKey): array;

    // public function getRecordColumn(int $recordKey, int $columnKey): mixed;

    abstract public function keyExist(int $recordKey): bool;
}
