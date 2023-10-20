<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats\Modules;

use CondorcetPHP\Oliphant\ColumnRepresentation;
use CondorcetPHP\Oliphant\Exceptions\NotYetImplementedException;
use CondorcetPHP\Oliphant\Stats\{ColumnStatsMethodInterface, ColumnStatsPropertyInterface};

class Average implements ColumnStatsMethodInterface, ColumnStatsPropertyInterface
{
    public const NAME = 'average';

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
        $sum = $column->sum();
        $count = $column->count();

        return $count > 0 ? $sum / $count : throw new NotYetImplementedException;
    }
}
