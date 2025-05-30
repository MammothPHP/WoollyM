<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\{DataFrameException, InvalidSelectException};
use Iterator;
use ArrayAccess;
use IteratorIterator;

abstract class DataFrameAccessors extends DataFramePrimitives implements ArrayAccess, Iterator
{
    /* *****************************************************************************************************************
     ******************************************** Array Conversion *****************************************************
     ******************************************************************************************************************/

    /**
     * Outputs a DataFrame as a two-dimensional associative array.
     */
    public function toArray(bool $fillInNonExistentCol = false): array
    {
        return iterator_to_array($this->getRecordsAsArrayIterator($fillInNonExistentCol), true);
    }

    /* *****************************************************************************************************************
     ******************************************* ArrayAccess Implementation ********************************************
     ******************************************************************************************************************/

    /**
     * Provides isset($df['column']) functionality.
     *
     * @internal
     */
    public function offsetExists(mixed $index): bool
    {
        return $this->recordKeyExist($index);
    }

    /**
     * @internal
     * @throws InvalidSelectException
     */
    public function offsetGet(mixed $index): Record
    {
        return $this->getRecord($index);
    }

    /**
     * @internal
     * @throws DataFrameException
     */
    public function offsetSet(mixed $index, mixed $row): void
    {
        \is_int($index) ? $this->updateRecord($index, $row) : $this->addRecord($row);
    }

    /**
     * Allows user to remove columns from the DataFrame using unset.
     *      ie: unset($df['column'])
     *
     * @internal
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->deleteRecord($offset);
    }

    /* *****************************************************************************************************************
     ********************************************* Iterator Implementation *********************************************
     ******************************************************************************************************************/

    protected function initDriverIterator(): void
    {
        $this->driverIterator = $this->data->getIterator();
    }

    /**
     * Return the current element
     * @internal
     * @link   http://php.net/manual/en/iterator.current.php
     */
    public function current(): Record
    {
        return $this->convertAbstractToRecordObject($this->driverIterator->current(), $this->key());
    }

    /**
     * Move forward to next element
     * @internal
     * @link   http://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        $this->driverIterator->next();
    }

    /**
     * Return the key of the current element
     * @internal
     * @link   http://php.net/manual/en/iterator.key.php
     */
    public function key(): mixed
    {
        return $this->driverIterator->key();
    }

    /**
     * Checks if current recordKey is valid
     * @internal
     * @link   http://php.net/manual/en/iterator.valid.php
     *                 Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return $this->driverIterator->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @internal
     * @link   http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->initDriverIterator();
    }

    /* *****************************************************************************************************************
     ********************************************* RecordArray Iterator ************************************************
     ******************************************************************************************************************/

    public function getRecordsAsArrayIterator(bool $fillAllColumn = false): Iterator
    {
        if ($fillAllColumn) {
            return new class ($this) extends IteratorIterator {
                public function current(): array
                {
                    return parent::current()->toContextualArray();
                }
            };
        } else {
            return new class ($this) extends IteratorIterator {
                public function current(): array
                {
                    return parent::current()->toArray();
                }
            };
        }
    }
}
