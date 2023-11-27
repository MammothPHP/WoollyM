<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

abstract class DataFrameHelpers extends DataFrameStatements
{
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
