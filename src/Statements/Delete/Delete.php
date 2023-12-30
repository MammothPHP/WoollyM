<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Delete;

use Closure;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\{SelectAllMode, Statement};

class Delete extends Statement
{
    /**
     * Remove record if closure return false
     * @param $f - ex: fn(array recordData, int $recordKey): bool => ...
     */
    public function filter(Closure $f): DataFrame
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
