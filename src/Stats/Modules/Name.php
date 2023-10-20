<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats\Modules;

use CondorcetPHP\Oliphant\ColumnRepresentation;
use CondorcetPHP\Oliphant\Stats\{ColumnStatsMethodInterface, ColumnStatsPropertyInterface};

class Name implements ColumnStatsPropertyInterface
{
    public const NAME = 'name';

    public function executeProperty(ColumnRepresentation $column): string
    {
        return $column->getName();
    }
}
