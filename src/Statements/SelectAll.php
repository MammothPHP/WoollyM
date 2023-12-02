<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

use MammothPHP\WoollyM\DataFrame;
use Override;

class SelectAll extends FixedSelect
{
    public function __construct(DataFrame $df)
    {
        $this->setLinkedDataFrame($df);
    }

    #[Override]
    public function getSelect(): array
    {
        return $this->getLinkedDataFrame()->columnsNames();
    }
}