<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\ColumnRepresentation;

interface ColumnStatsPropertyInterface extends StatsInterface
{
    public function executeProperty(ColumnRepresentation $column): mixed;
}
