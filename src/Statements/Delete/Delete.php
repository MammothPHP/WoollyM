<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Delete;

use Closure;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\{SelectAllMode, Statement};

class Delete extends Statement
{
    use SelectAllMode;

    /**
     * Delete all the records from a statement
     */
    public function execute(): DataFrame
    {
        return $this->filter(static fn(): bool => true);
    }

    /**
     * Delete a record by key
     * @throws KeyNotExistException
     */
    public function record(int $key): DataFrame
    {
        return $this->getLinkedDataFrame()->removeRecord($key);
    }

    /**
     * Remove record if closure return true
     * @param $f - ex: fn(array recordData, int $recordKey): bool => ...
     */
    public function filter(Closure $f): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        foreach ($this as $recordKey => $recordData) {
            if ($f($recordData, $recordKey) === true) {
                $df->removeRecord($recordKey);
            }
        }

        return $df;
    }
}
