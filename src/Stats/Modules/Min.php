<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

class Min extends AbstractMinMaxMax
{
    public const string NAME = 'min';

    protected function compare(int|float $a, int|float $b): bool
    {
        return $a < $b;
    }
}
