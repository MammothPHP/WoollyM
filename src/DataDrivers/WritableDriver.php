<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers;

interface WritableDriver extends DataDriver
{
    public function setRecord(int $recordKey, array $recordData): void;

    public function setRecordColumn(int $recordKey, int|string $columnKey, mixed $colValue): void;

    public function addRecord(array $recordData): void;

    public function removeRecord(int $recordKey): void;
}
