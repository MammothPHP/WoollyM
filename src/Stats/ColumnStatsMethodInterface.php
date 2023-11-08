<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\ColumnRepresentation;

interface ColumnStatsMethodInterface extends StatsInterface
{
    public function executeMethod(ColumnRepresentation $column, array $arguments): mixed;
}
