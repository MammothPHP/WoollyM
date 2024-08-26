<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\ModuleTypes;

use MammothPHP\WoollyM\Stats\AggProvider;

interface AggInterface
{
    public static function col(string $column, ?string $as = null): AggProvider;

    public function addValue(mixed $value): void;

    public function getResult(): mixed;
}
