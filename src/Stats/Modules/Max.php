<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

class Max extends MinMaxMaxAbstract
{
    public const string NAME = 'max';

    protected function compare(mixed $a, mixed $b): bool
    {
        return $this->greaterThan($a, $b);
    }

    protected function greaterThan(mixed $a, mixed $b): bool
    {
        if ($a === null) {
            return false;
        }

        if ($b === null) {
            return true;
        }

        if (\is_bool($b) && !\is_bool($a)) {
            return true;
        }

        return $a > $b;
    }
}
