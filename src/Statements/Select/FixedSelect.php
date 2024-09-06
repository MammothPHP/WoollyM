<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Select;

use MammothPHP\WoollyM\Exceptions\UnavailableMethodInContext;
use MammothPHP\WoollyM\Stats\AggProvider;
use Override;

abstract class FixedSelect extends Select
{
    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function select(string|AggProvider ...$selections): static
    {
        throw new UnavailableMethodInContext;
    }

    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function replaceSelect(string ...$selections): static
    {
        throw new UnavailableMethodInContext;
    }

    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function reset(): static
    {
        return $this->resetWhere()->resetLimit();
    }

    /**
     * @ignore
     * @internal
     */
    #[Override]
    public function resetSelect(): static
    {
        throw new UnavailableMethodInContext;
    }
}
