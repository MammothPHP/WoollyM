<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Countable;

abstract class DataFrameHelpers extends DataFrameStatements implements Countable
{
    /* *****************************************************************************************************************
     ******************************************** Countable Implementation *********************************************
     ******************************************************************************************************************/

    /**
     * Count elements of an object
     *
     * @link   http://php.net/manual/en/countable.count.php
     *      */
    public function count(): int
    {
        return $this->data->count();
    }

    /* *****************************************************************************************************************
     ******************************************** Stats ****************************************************************
     ******************************************************************************************************************/

    public function head(int $length = 5, int $offset = 0, array|string|null $columns = null): array
    {
        if (\is_string($columns)) {
            $columns = [$columns];
        }

        $select = ($columns === null) ? $this->selectAll() : $this->select(...$columns);

        return $select->limit($length, $offset)->toArray();
    }
}
