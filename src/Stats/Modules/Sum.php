<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats\Modules;

use CondorcetPHP\Oliphant\ColumnRepresentation;
use CondorcetPHP\Oliphant\Stats\ColumnStatsMethodInterface;
use CondorcetPHP\Oliphant\Stats\ColumnStatsPropertyInterface;

class Sum implements ColumnStatsPropertyInterface, ColumnStatsMethodInterface
{
    public const NAME = 'sum';

    public function executeProperty (ColumnRepresentation $column): int|float
    {
        return $this->execute($column);
    }

    public function executeMethod (ColumnRepresentation $column, array $arguments): int|float
    {
        return $this->execute($column);
    }

    protected function execute (ColumnRepresentation $column): int|float
    {
        $r = 0;
        $columnName = $column->getName();

        foreach ($column->getDataFrame()->getColumn($columnName) as $value) {
            if (!empty($value[$columnName])) {
                $r += $value[$columnName];
            }
        }

        return $r;
    }
}