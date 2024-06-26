<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

class Max extends AbstractMinMaxMax
{
    public const string NAME = 'max';

    protected function compare(int|float $a, int|float $b): bool
    {
        return $a > $b;
    }
}
