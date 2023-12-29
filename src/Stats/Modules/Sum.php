<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

class Sum implements StatsMethodInterface, StatsPropertyInterface
{
    public const string NAME = 'sum';

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
        $r = 0;

        foreach ($select as $record) {
            foreach ($record as $value) {
                if ($value === true) {
                    $value = 1;
                }

                if (!empty($value) && is_numeric($value)) {
                    if (\is_string($value) && ctype_digit($value)) {
                        $value = \intval($value);
                    } elseif (!\is_int($value)) {
                        $value = \floatval($value);
                    }

                    $r += $value;
                }
            }
        }

        return $r;
    }
}
