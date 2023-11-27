<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

class Count implements StatsMethodInterface, StatsPropertyInterface
{
    public const string NAME = 'count';

    public function executeProperty(Select $select): int|float
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): int|float
    {
        return $this->execute($select, ...$arguments);
    }

    protected function execute(Select $select, bool $ignoreNonNumeric = false): int|float
    {
        $r = 0;

        foreach ($select as $record) {
            foreach ($record as $value) {
                if ($value !== null) {
                    if ($ignoreNonNumeric) {
                        if (is_numeric($value) || $value === true) {
                            $r++;
                        }
                    } else {
                        $r++;
                    }
                }
            }
        }

        return $r;
    }
}
