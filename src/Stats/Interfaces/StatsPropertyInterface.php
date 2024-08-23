<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Interfaces;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\StatsInterface;

interface StatsPropertyInterface extends StatsInterface
{
    public function executeProperty(Select $column): mixed;
}
