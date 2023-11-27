<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Statements\{ColumnRepresentation, Select, SelectAll};

abstract class DataFrameStatements extends DataFrameModifiers
{
    /* *****************************************************************************************************************
     ******************************************* Statements ********************************************
     ******************************************************************************************************************/

    public function select(string ...$selections): Select
    {
        return new Select($this, ...$selections);
    }

    public function selectAll(): SelectAll
    {
        return new SelectAll($this);
    }

    public function col(string $columnName): ColumnRepresentation
    {
        return $this->columnRepresentations[$this->getColumnIndexObject($columnName)];
    }

    public function column(string $columnName): ColumnRepresentation
    {
        return $this->col($columnName);
    }
}
