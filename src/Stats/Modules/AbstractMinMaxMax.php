<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

abstract class AbstractMinMaxMax implements StatsMethodInterface, StatsPropertyInterface
{
    public function executeProperty(Select $select): mixed
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): mixed
    {
        return $this->execute($select, ...$arguments);
    }

    abstract protected function compare(mixed $a, mixed $b): bool;

    protected function execute(Select $select): mixed
    {
        $first = true;
        foreach ($select as $record) {
            foreach ($record as $value) {
                if ($first) {
                    $first = false;
                    $r = $value;
                }

                if ($this->compare($value, $r)) {
                    $r = $value;
                }
            }
        }

        return $r;
    }
}
