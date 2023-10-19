<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats;

use CondorcetPHP\Oliphant\ColumnRepresentation;

interface ColumnStatsMethodInterface extends StatsInterface
{
    public function executeMethod(ColumnRepresentation $column, array $arguments): mixed;
}