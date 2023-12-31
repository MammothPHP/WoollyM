<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Delete;

use Closure;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\Statement;

class Delete extends Statement
{
    /**
     * Delete a record by key
     * @throws KeyNotExistException
     */
    public function record(int $key): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        return $df->removeRecord($key);
    }

    /**
     * Remove record if closure return false
     * @param $f - ex: fn(array recordData, int $recordKey): bool => ...
     */
    public function applyFilter(Closure $f): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        foreach ($df as $recordKey => $recordData) {
            if ($f($recordData, $recordKey) === false) {
                $df->removeRecord($recordKey);
            }
        }

        return $this->getLinkedDataFrame();
    }
}
