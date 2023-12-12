<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers;

use Countable;
use IteratorAggregate;

interface DataDriverInterface extends Countable, IteratorAggregate
{
    public const ColumnKeyType COLUMN_KEY_TYPE = ColumnKeyType::COLUMN_KEY;

    public function getRecordKey(int $recordKey): array;

    // public function getRecordColumn(int $recordKey, int $columnKey): mixed;

    public function setRecord(int $recordKey, array $recordData): void;

    public function setRecordColumn(int $recordKey, int|string $columnKey, mixed $colValue): void;

    public function addRecord(array $recordData): void;

    public function removeRecord(int $recordKey): void;

    public function keyExist(int $recordKey): bool;
}
