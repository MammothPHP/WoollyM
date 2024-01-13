<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

class Size implements StatsMethodInterface, StatsPropertyInterface
{
    public const string NAME = 'size';

    public function executeProperty(Select $select): int
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): int
    {
        return $this->execute($select, ...$arguments);
    }

    protected function execute(Select $select): int
    {
        $r = 0;

        foreach ($select as $record) {
            $r += \count($record);
        }

        return $r;
    }
}
