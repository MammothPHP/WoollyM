<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\StatsModuleTypes;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\StatsInterface;

interface StatsMethodInterface extends StatsInterface
{
    public function executeMethod(Select $column, array $arguments): mixed;
}
