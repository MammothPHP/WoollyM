<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Bases;

readonly class Group
{
    public static function col(string $col): self
    {
        return new self($col);
    }

    public function __construct(public readonly string $col) {}
}
