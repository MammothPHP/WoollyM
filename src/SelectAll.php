<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\UnavailableMethodInContext;
use Override;

class SelectAll extends Select
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

    #[Override]
    public function replaceSelect(string ...$selections): self
    {
        throw new UnavailableMethodInContext;
    }

    #[Override]
    public function reset(): self
    {
        return $this->resetWhere()->resetLimit();
    }

    #[Override]
    public function resetSelect(): self
    {
        throw new UnavailableMethodInContext;
    }
}
