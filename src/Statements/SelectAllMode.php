<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

use MammothPHP\WoollyM\DataFrame;

/**
 * @internal
 */
trait SelectAllMode
{
    public function __construct(DataFrame $df)
    {
        $this->setLinkedDataFrame($df);
    }

    public function getSelect(bool $forceString = false): array
    {
        return $this->getLinkedDataFrame()->columnsNames();
    }
}
