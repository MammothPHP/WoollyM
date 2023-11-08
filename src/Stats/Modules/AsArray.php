<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\{ColumnRepresentation, DataFrame, DataFrameCore};
use MammothPHP\WoollyM\Stats\{ColumnStatsMethodInterface, ColumnStatsPropertyInterface};

class AsArray implements ColumnStatsMethodInterface, ColumnStatsPropertyInterface
{
    public const NAME = 'asArray';

    public function executeProperty(ColumnRepresentation $column): array
    {
        return $this->execute($column);
    }

    public function executeMethod(ColumnRepresentation $column, array $arguments): array
    {
        return $this->execute($column);
    }

    protected function execute(ColumnRepresentation $column): array
    {
        $data = [];
        $colName = $column->getName();

        foreach ($column->getLinkedDataFrame() as $row) {
            $data[] = [$colName => $row[$colName]];
        }

        return $data;
    }
}
