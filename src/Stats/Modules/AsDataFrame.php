<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats\Modules;

use CondorcetPHP\Oliphant\ColumnRepresentation;
use CondorcetPHP\Oliphant\DataFrame;
use CondorcetPHP\Oliphant\DataFrameCore;
use CondorcetPHP\Oliphant\Stats\{ColumnStatsMethodInterface, ColumnStatsPropertyInterface};

class AsDataFrame implements ColumnStatsMethodInterface, ColumnStatsPropertyInterface
{
    public const NAME = 'asDataFrame';

    public function executeProperty(ColumnRepresentation $column): DataFrameCore
    {
        return $this->execute($column);
    }

    public function executeMethod(ColumnRepresentation $column, array $arguments): DataFrameCore
    {
        return $this->execute($column);
    }

    protected function execute(ColumnRepresentation $column): DataFrameCore
    {
        $data = [];
        $colName = $column->getName();

        foreach ($column->getLinkedDataFrame() as $row) {
            $data[] = [$colName => $row[$colName]];
        }

        return new DataFrame($data);
    }
}
