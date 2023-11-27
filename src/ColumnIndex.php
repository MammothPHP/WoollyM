<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use WeakReference;

class ColumnIndex
{
    public readonly WeakReference $df;
    public ?DataType $forcedType = null;

    public function __construct(public string $name, DataFrame $df)
    {
        $this->df = WeakReference::create($df);
    }
}
