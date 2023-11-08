<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Stringable;
use WeakReference;

class ColumnIndex implements Stringable
{
    public readonly WeakReference $df;
    public ?DataType $forcedType = null;

    public function __construct(public string $name, DataFrame $df)
    {
        $this->df = WeakReference::create($df);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
