<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Sort;

abstract readonly class Sort
{
    final public function __construct(public readonly string $col) {}

    public static function col(string $col): static
    {
        return new static ($col);
    }
}
