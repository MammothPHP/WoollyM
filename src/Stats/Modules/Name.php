<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\ColumnRepresentation;
use MammothPHP\WoollyM\Stats\{ColumnStatsMethodInterface, ColumnStatsPropertyInterface};

class Name implements ColumnStatsPropertyInterface
{
    public const NAME = 'name';

    public function executeProperty(ColumnRepresentation $column): string
    {
        return $column->getName();
    }
}
