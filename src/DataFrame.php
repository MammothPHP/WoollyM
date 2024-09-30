<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

// Hierarchy: DataFramePrimitives > DataFrameAccessors >  DataFrameStatements > DataFrameModifiers > DataFrameHelpers
class DataFrame extends DataFrameHelpers
{
    /**
     * Factory method for creating a DataFrame from a two-dimensional associative array.
     */
    public static function fromArray(array $data): static
    {
        return new static($data); // @phpstan-ignore new.static
    }
}
