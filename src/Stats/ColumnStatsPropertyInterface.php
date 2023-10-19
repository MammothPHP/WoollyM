<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats;

use CondorcetPHP\Oliphant\ColumnRepresentation;

interface ColumnStatsPropertyInterface extends StatsInterface
{
    public function executeProperty(ColumnRepresentation $column): mixed;
}