<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats\Modules;

use CondorcetPHP\Oliphant\ColumnRepresentation;
use CondorcetPHP\Oliphant\Exceptions\NotYetImplementedException;
use CondorcetPHP\Oliphant\Stats\ColumnStatsMethodInterface;
use CondorcetPHP\Oliphant\Stats\ColumnStatsPropertyInterface;

class Name implements ColumnStatsPropertyInterface
{
    public const NAME = 'name';

    public function executeProperty (ColumnRepresentation $column): string
    {
        return $column->getName();
    }
}