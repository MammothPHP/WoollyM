<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Delete;

use Closure;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\SelectAllMode;
use MammothPHP\WoollyM\Statements\Statement;

class Delete extends Statement
{
    use SelectAllMode;

    /**
     * Delete a record by key
     * @throws KeyNotExistException
     */
    public function record(int $key): DataFrame
    {
        return $this->getLinkedDataFrame()->removeRecord($key);
    }

    /**
     * Remove record if closure return false
     * @param $f - ex: fn(array recordData, int $recordKey): bool => ...
     */
    public function applyFilter(Closure $f): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        foreach ($this as $recordKey => $recordData) {
            if ($f($recordData, $recordKey) === false) {
                $df->removeRecord($recordKey);
            }
        }

        return $df;
    }
}
