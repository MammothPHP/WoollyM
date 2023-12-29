<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Sort;

use MammothPHP\WoollyM\{DataFrame, LinkedDataFrame};

class Sort
{
    use LinkedDataFrame;

    public function __construct(DataFrame $df)
    {
        $this->setLinkedDataFrame($df);
    }
}
