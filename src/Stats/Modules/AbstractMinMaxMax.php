<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

abstract class AbstractMinMaxMax extends AbstractAgg
{
    protected bool $isFirst = true;

    abstract protected function compare(int|float $a, int|float $b): bool;

    public function getResult(): int|float|null
    {
        return $this->isFirst ? null : $this->agg;
    }

    public function addValue(mixed $value): void
    {
        if (!\is_int($value) && !\is_float($value)) {
            return;
        }

        if ($this->isFirst) {
            $this->isFirst = false;
            $this->agg = $value;
        }

        if ($this->compare($value, $this->agg)) {
            $this->agg = $value;
        }
    }
}
