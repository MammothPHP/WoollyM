<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Countable;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, NotYetImplementedException};

abstract class DataFrameHelpers extends DataFrameModifiers implements Countable
{
    /* *****************************************************************************************************************
     ******************************************** Countable Implementation *********************************************
     ******************************************************************************************************************/

    /**
     * Count records
     */
    public function count(): int
    {
        return $this->data->count();
    }

    /* *****************************************************************************************************************
     ******************************************** Stats ****************************************************************
     ******************************************************************************************************************/

    /**
     * Return the the first records
     * @param $length - Number of records
     * @param $offset - Start at record number x (from 0)
     * @param $columns - Only these columns
     * @throws InvalidSelectException
     * @throws NotYetImplementedException
     */
    public function head(int $length = 5, int $offset = 0, array|string|null $columns = null): array
    {
        if (\is_string($columns)) {
            $columns = [$columns];
        }

        $select = ($columns === null) ? $this->selectAll() : $this->select(...$columns);

        return $select->limit($length, $offset)->toArray();
    }


    /* *****************************************************************************************************************
     ******************************************** Group ****************************************************************
     ******************************************************************************************************************/


    /**
     * @param string[] $args
     * @return DataFrame
     */
    public function group(string ...$args): DataFrame
    {
        return (new Extract($this, new DataFrame))->group(...$args);
    }
}
