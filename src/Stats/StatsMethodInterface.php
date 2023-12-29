<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\Statements\Select\Select;

interface StatsMethodInterface extends StatsInterface
{
    public function executeMethod(Select $column, array $arguments): mixed;
}
