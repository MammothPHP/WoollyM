<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\IO\Wrappers\{SqlWrapper, XlsxWrapper};

// Hierarchy: DataFramePrimitives > DataFrameAccessors >  DataFrameStatements > DataFrameModifiers > DataFrameHelpers
class DataFrame extends DataFrameHelpers
{
    use SqlWrapper;
    use XlsxWrapper;

    /**
     * Factory method for creating a DataFrame from a two-dimensional associative array.
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
