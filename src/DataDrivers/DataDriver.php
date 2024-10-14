<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers;

use Countable;
use Iterator;
use IteratorAggregate;

interface DataDriver extends Countable, IteratorAggregate
{
    public const ColumnKeyType COLUMN_KEY_TYPE = ColumnKeyType::COLUMN_KEY;

    public function getIterator(): Iterator;

    public function getRecordKey(int $recordKey): array;

    // public function getRecordColumn(int $recordKey, int $columnKey): mixed;

    public function keyExist(int $recordKey): bool;
}
