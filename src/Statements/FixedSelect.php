<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

use MammothPHP\WoollyM\Exceptions\UnavailableMethodInContext;
use Override;

abstract class FixedSelect extends Select
{
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
