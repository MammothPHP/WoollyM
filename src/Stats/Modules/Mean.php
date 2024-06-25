<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;

class Mean extends AbstractAgg
{
    public const string NAME = 'mean';

    protected readonly Sum $sum;
    protected readonly Size $size;

    public function getResult(): int|float
    {
        return $this->size->getResult() > 0 ? ($this->sum->getResult() / $this->size->getResult()) : throw new NotYetImplementedException;
    }

    public function addValue(mixed $value): void
    {
        if (!isset($this->sum)) {
            $this->sum = new Sum;
            $this->size = new Size;
            $this->size->init(ignoreNonNumericValue: true);
        }

        $this->sum->addValue($value);
        $this->size->addvalue($value);
    }

}
