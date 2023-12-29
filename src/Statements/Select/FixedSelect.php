<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Select;

use MammothPHP\WoollyM\Exceptions\UnavailableMethodInContext;
use Override;

abstract class FixedSelect extends Select
{
    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function select(string ...$selections): self
    {
        throw new UnavailableMethodInContext;
    }

    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function replaceSelect(string ...$selections): self
    {
        throw new UnavailableMethodInContext;
    }

    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function reset(): self
    {
        return $this->resetWhere()->resetLimit();
    }

    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function resetSelect(): self
    {
        throw new UnavailableMethodInContext;
    }
}
