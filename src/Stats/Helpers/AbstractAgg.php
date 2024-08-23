<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Helpers;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\Helpers\AggGroup;
use MammothPHP\WoollyM\Stats\Interfaces\AggInterface;
use MammothPHP\WoollyM\Stats\Interfaces\StatsMethodInterface;
use MammothPHP\WoollyM\Stats\Interfaces\StatsPropertyInterface;

abstract class AbstractAgg implements AggInterface, StatsMethodInterface, StatsPropertyInterface
{
    use AggGroup;

    protected int|float $agg = 0;

    public function executeProperty(Select $select): int|float|null
    {
        $this->execute($select);

        return $this->getResult();
    }

    public function executeMethod(Select $select, array $arguments): int|float|null
    {
        $this->execute($select);

        return $this->getResult();
    }

    abstract public function addValue(mixed $value): void;

    public function getResult(): int|float|null
    {
        return $this->agg;
    }

    protected function execute(Select $select): void
    {
        foreach ($select as $record) {
            foreach ($record as $value) {
                $this->addValue($value);
            }
        }
    }
}
