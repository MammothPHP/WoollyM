<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use ArrayAccess;
use Countable;
use Iterator;
use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use MammothPHP\WoollyM\Exceptions\SourceDataFrameNoLongerExist;
use WeakReference;

class Record implements ArrayAccess, Countable, Iterator
{
    public readonly WeakReference $dataFrame;

    public function __construct(
        DataFrame $dataFrame,
        public readonly int|string $recordKey,
        protected array $recordArray
    ) {
        $this->dataFrame = WeakReference::create($dataFrame);
    }

    public function toArray(): array
    {
        return $this->recordArray;
    }

    public function toContextualArray(): array
    {
        $df = $this->getDataFrame();

        if ($df === null) {
            throw new SourceDataFrameNoLongerExist('Source DataFrame no longer exist');
        }

        $r = [];

        foreach ($df->columnsNames() as $columnName) {
            if (!$this->hasColumn($columnName)) {
                $r[$columnName] = null;
            } else {
                $r[$columnName] = $this->recordArray[$columnName];
            }
        }

        return $r;
    }

    public function hasColumn(string $columnName): bool
    {
        return $this->offsetExists($columnName);
    }

    public function getDataFrame(): ?DataFrame
    {
        return $this->dataFrame->get();
    }

    // Implement Coutable

    public function count(): int
    {
        return \count($this->recordArray);
    }

    // Implement ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->recordArray);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->recordArray[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new NotYetImplementedException('Record object are immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new NotYetImplementedException('Record object are immutable');
    }


    // Implement Iterator

    public function rewind(): void
    {
        reset($this->recordArray);
    }

    public function current(): mixed
    {
        return current($this->recordArray);
    }

    public function key(): ?string
    {
        return key($this->recordArray);
    }

    public function next(): void
    {
        next($this->recordArray);
    }

    public function valid(): bool
    {
        $key = $this->key();

        return $key === null ? false : \array_key_exists($this->key(), $this->recordArray);
    }
}
