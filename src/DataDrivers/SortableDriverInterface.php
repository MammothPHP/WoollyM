<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\DataDrivers;

use Closure;
use Countable;
use IteratorAggregate;

interface SortableDriverInterface extends Countable, IteratorAggregate
{
    public function usort(Closure $callback): void;
}
