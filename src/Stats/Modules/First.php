<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Stats\Bases\AbstractAgg;

class First extends AbstractAgg
{
    public const string NAME = 'first';

    protected mixed $first = null;

    public function getResult(): mixed
    {
        return $this->first;
    }

    public function addValue(mixed $value): void
    {
        $this->first ??= $value;
    }

}
