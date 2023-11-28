<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\{DataFrameException, InvalidSelectException};
use Iterator;
use ArrayAccess;

abstract class DataFrameAccessors extends DataFramePrimitives implements ArrayAccess, Iterator
{
    /* *****************************************************************************************************************
     ******************************************** Array Conversion *****************************************************
     ******************************************************************************************************************/

    /**
     * Outputs a DataFrame as a two-dimensional associative array.
     */
    public function toArray(): array
    {
        return iterator_to_array($this, true);
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
     * Allows user retrieve DataFrame subsets from a two-dimensional array by
     * simply requesting an element of the instantiated DataFrame.
     *      ie: $fooDF = $df['foo'];
     *
     * @internal
     * @throws InvalidSelectException
     */
    public function offsetGet(mixed $index): mixed
    {
        return $this->data->getRecordKey($index);

        // $this->mustHaveColumn($columnName);

        // $data = [];

        // foreach ($this as $row) {
        //     $data[] = [$columnName => $row[$columnName]];
        // }

        // return new DataFrame($data);
    }

    /**
     * Allows user set DataFrame columns from a Closure, value, array, or another single-column DataFrame.
     *      ie:
     *          $df[$targetColumn] = $rightHandSide
     *          $df['bar'] = $df['foo'];
     *          $df['bar'] = $df->foo;
     *          $df['foo'] = function ($foo) { return $foo + 1; };
     *          $df['foo'] = 'bar';
     *          $df[] = [['gender'=>'Female','name'=>'Luy'],['title'=>'Mr','name'=>'Noah']];
     *
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
     * @throws InvalidSelectException
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeRecord($offset);
    }

    /* *****************************************************************************************************************
     ********************************************* Iterator Implementation *********************************************
     ******************************************************************************************************************/

    /**
    * Return the current element
    *
    * @link   http://php.net/manual/en/iterator.current.php
    */
    public function current(): mixed
    {
        return $this->getRecord($this->key());
    }

    /**
     * Move forward to next element
     *
     * @link   http://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        $this->driverIterator->next();
    }

    /**
     * Return the key of the current element
     *
     * @link   http://php.net/manual/en/iterator.key.php
     */
    public function key(): mixed
    {
        return $this->driverIterator->key();
    }

    /**
     * Checks if current recordKey is valid
     *
     * @link   http://php.net/manual/en/iterator.valid.php
     *                 Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return $this->driverIterator->valid();
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link   http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->initDriverIterator();
    }
}
