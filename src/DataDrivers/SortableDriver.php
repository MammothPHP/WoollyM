<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers;

use Closure;
use Countable;
use IteratorAggregate;

interface SortableDriver extends Countable, IteratorAggregate
{
    public function uasort(Closure $callback): void;
}
