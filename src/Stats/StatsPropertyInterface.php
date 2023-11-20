<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\Select;

interface StatsPropertyInterface extends StatsInterface
{
    public function executeProperty(Select $column): mixed;
}
