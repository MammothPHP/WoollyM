<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Iterators;

use Iterator;
use MammothPHP\WoollyM\Record;
use MammothPHP\WoollyM\Statements\Statement;

class StatementRegularIterator implements Iterator
{
    public function __construct (public readonly Statement $statement) {}

    // Iterator

    protected function moveToNextValidRecord(): void
    {
        if ($this->valid()) {
            if (!$this->statement->passWhereStatement($this->key(), $this->currentUnfiltered())) {
                $this->next();
            }
        }
    }

    /**
     * @internal
     */
    public function rewind(): void
    {
        $this->statement->getLinkedDataFrame()->rewind();
    }

    protected function getRecordArrayEligibleColumns(Record $record): array
    {
        return $record->toArray();
    }

    /**
     * @internal
     */
    public function current(): mixed
    {
        $r = $this->statement->getLinkedDataFrame()->current();

        return $this->getRecordArrayEligibleColumns($r);
    }

    /**
     * @internal
     */
    protected function currentUnfiltered(): array
    {
        return $this->statement->getLinkedDataFrame()->current()->toArray();
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
    }

    /**
     * @internal
     */
    public function valid(): bool
    {
        return $this->statement->getLinkedDataFrame()->valid();
    }
}