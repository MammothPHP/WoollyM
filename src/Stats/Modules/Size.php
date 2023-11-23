<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

class Size implements StatsMethodInterface, StatsPropertyInterface
{
    public const NAME = 'size';

    public function executeProperty(Select $select): int|float
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): int|float
    {
        return $this->execute($select, ...$arguments);
    }

    protected function execute(Select $select): int|float
    {
        $r = 0;

        foreach ($select as $record) {
            foreach ($record as $value) {
                $r++;
            }
        }

        return $r;
    }
}
