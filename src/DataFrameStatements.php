<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\InvalidSelectException;
use MammothPHP\WoollyM\Statements\Select\{ColumnRepresentation, Select, SelectAll};
use MammothPHP\WoollyM\Stats\AggProvider;

abstract class DataFrameStatements extends DataFrameAccessors
{
    /* *****************************************************************************************************************
     ******************************************* Statements ************************************************************
     ******************************************************************************************************************/

    /**
     * Return a Select object
     * @param string|AggProvider $selections column(s) name(s) to select
     */
    public function select(string|AggProvider ...$selections): Select
    {
        return new Select($this, ...$selections);
    }

    /**
     * Return a fixed selectAll object
     */
    public function selectAll(): SelectAll
    {
        return new SelectAll($this);
    }

    /**
     * Return a ColumnRepresentation object, extending Select object.
     * @throws InvalidSelectException
     */
    public function col(string $columnName): ColumnRepresentation
    {
        return $this->columnRepresentations[$this->getColumnIndexObject($columnName)];
    }

    /**
     * Alias for col() method.
     */
    public function column(string $columnName): ColumnRepresentation
    {
        return $this->col($columnName);
    }
}
