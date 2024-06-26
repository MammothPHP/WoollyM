<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

trait AggGroup
{
    public static function col(string $column, ?string $as = null): AggProvider
    {
        return new AggProvider($column, static::class, $as);
    }
}
