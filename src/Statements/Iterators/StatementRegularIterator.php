<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Iterators;

use Iterator;
use MammothPHP\WoollyM\Record;
use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Statements\Statement;

class StatementRegularIterator implements Iterator
{
    protected const bool UNFILTERED_COLUMN = false;

    public function __construct(public readonly Statement $statement) {}

    // Iterator

    /**
     * @internal
     */
    public function rewind(): void
    {
        $this->statement->getLinkedDataFrame()->rewind();
        $this->moveToNextValidRecord();
    }

    /**
     * @internal
     */
    public function current(): mixed
    {
        $recordArray = $this->currentRecord()->toArray();

        if (!static::UNFILTERED_COLUMN && $this->statement instanceof Select) {
            $r = [];

            foreach ($this->statement->getSelect(true) as $columnName) {
                $r[$columnName] = $recordArray[$columnName] ?? null;
            }

            return $r;
        }

        return $recordArray;
    }

    /**
     * @internal
     */
    public function currentRecord(): Record
    {
        return $this->statement->getLinkedDataFrame()->current();
    }

    /**
     * @internal
     */
    public function key(): int
    {
        return $this->statement->getLinkedDataFrame()->key();
    }

    /**
     * @internal
     */
    public function next(): void
    {
        $this->statement->getLinkedDataFrame()->next();

        $this->moveToNextValidRecord();
    }

    protected function moveToNextValidRecord(): void
    {
        if ($this->valid()) {
            if (!$this->statement->passWhereStatement($this->key(), $this->currentRecord())) {
                $this->next();
            }
        }
    }

    /**
     * @internal
     */
    public function valid(): bool
    {
        return $this->statement->getLinkedDataFrame()->valid();
    }
}
