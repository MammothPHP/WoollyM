<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\ModuleTypes;

use MammothPHP\WoollyM\Statements\Select\Select;

interface StatsPropertyInterface extends StatsInterface
{
    public function executeProperty(Select $column): mixed;
}
