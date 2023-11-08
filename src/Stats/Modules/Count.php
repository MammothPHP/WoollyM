<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\ColumnRepresentation;
use MammothPHP\WoollyM\Stats\{ColumnStatsMethodInterface, ColumnStatsPropertyInterface};

class Count implements ColumnStatsMethodInterface, ColumnStatsPropertyInterface
{
    public const NAME = 'count';

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
            if (!empty($value[$columnName])) {
                $r += 1;
            }
        }

        return $r;
    }
}
