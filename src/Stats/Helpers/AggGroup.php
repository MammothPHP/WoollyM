<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Helpers;

use MammothPHP\WoollyM\Stats\AggProvider;

trait AggGroup
{
    public static function col(string $column, ?string $as = null): AggProvider
    {
        return new AggProvider($column, static::class, $as);
    }
}
