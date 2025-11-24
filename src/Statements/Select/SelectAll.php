<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Select;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\SelectAllMode;

class SelectAll extends FixedSelect
{
    use SelectAllMode;

    public function __construct(DataFrame $df)
    {
        $this->groupBy = new \WeakMap;
        $this->setLinkedDataFrame($df);
    }
}
