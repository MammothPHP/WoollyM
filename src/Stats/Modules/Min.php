<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

class Min extends AbstractMinMaxMax
{
    public const string NAME = 'min';

    protected function compare(mixed $a, mixed $b): bool
    {
        return $this->lessThan($a, $b);
    }

    protected function lessThan(mixed $a, mixed $b): bool
    {
        if ($b === null) {
            return false;
        }

        if ($a === null) {
            return true;
        }

        if (\is_bool($b) && !\is_bool($a)) {
            return false;
        }

        return $a < $b;
    }
}
