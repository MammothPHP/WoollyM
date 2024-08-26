<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Stats\Bases\AbstractAgg;

class Sum extends AbstractAgg
{
    public const string NAME = 'sum';

    public function addValue(mixed $value): void
    {
        if ($value === true) {
            $value = 1;
        }

        if (!empty($value) && is_numeric($value)) {
            if (\is_string($value) && ctype_digit($value)) {
                $value = \intval($value);
            } elseif (!\is_int($value)) {
                $value = \floatval($value);
            }

            $this->agg += $value;
        }
    }
}
