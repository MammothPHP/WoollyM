<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant;

use Stringable;

class Column implements Stringable
{
    public function __construct(public string $name)
    {}

    public function __toString(): string
    {
        return $this->name;
    }
}