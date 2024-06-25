<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

interface AggInterface
{
    public function addValue(mixed $value): void;

    public function getResult(): mixed;
}
