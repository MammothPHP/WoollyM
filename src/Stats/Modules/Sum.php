<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats\Modules;

use CondorcetPHP\Oliphant\ColumnRepresentation;
use CondorcetPHP\Oliphant\Stats\{ColumnStatsMethodInterface, ColumnStatsPropertyInterface};

class Sum implements ColumnStatsMethodInterface, ColumnStatsPropertyInterface
{
    public const NAME = 'sum';

    public function executeProperty(ColumnRepresentation $column): int|float
    {
        return $this->execute($column);
    }

    public function executeMethod(ColumnRepresentation $column, array $arguments): int|float
    {
        return $this->execute($column);
    }

    protected function execute(ColumnRepresentation $column): int|float
    {
        $r = 0;
        $columnName = $column->getName();

        foreach ($column->asDataFrame() as $value) {
            if ($value[$columnName] === true) {
                $value[$columnName] = 1;
            }

            if (!empty($value[$columnName]) && is_numeric($value[$columnName])) {
                $r += $value[$columnName];
            }
        }

        return $r;
    }
}
