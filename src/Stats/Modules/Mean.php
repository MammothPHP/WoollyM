<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use MammothPHP\WoollyM\Statements\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

class Mean implements StatsMethodInterface, StatsPropertyInterface
{
    public const string NAME = 'mean';

    public function executeProperty(Select $select): int|float
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): int|float
    {
        return $this->execute($select);
    }

    protected function execute(Select $select): int|float
    {
        $sum = $select->sum();
        $count = $select->count(true);

        return $count > 0 ? ($sum / $count) : throw new NotYetImplementedException;
    }
}
