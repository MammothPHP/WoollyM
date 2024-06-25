<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\{AggInterface, StatsMethodInterface, StatsPropertyInterface};

abstract class AbstractAgg implements AggInterface, StatsMethodInterface, StatsPropertyInterface
{
    protected int|float $agg = 0;

    public function executeProperty(Select $select): int|float
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): int|float
    {
        return $this->execute($select);
    }

    protected function reset(): void
    {
        $this->agg = 0;
    }

    abstract public function addValue(mixed $value): void;

    public function getResult(): int|float
    {
        return $this->agg;
    }

    protected function execute(Select $select): int|float
    {
        $this->reset();

        foreach ($select as $record) {
            foreach ($record as $value) {
                $this->addValue($value);
            }
        }

        return $this->getResult();
    }
}
