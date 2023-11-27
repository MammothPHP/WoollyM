<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\IO\Wrappers\{CsvWrapper, FwfWrapper, HtmlWrapper, JsonWrapper, SqlWrapper, XlsxWrapper};

// Hierarchy: DataFramePrimitives > DataFrameAccessors > DataFrameModifiers > DataFrameStatements > DataFrameHelpers
class DataFrame extends DataFrameHelpers
{
    use CsvWrapper;
    use FwfWrapper;
    use HtmlWrapper;
    use JsonWrapper;
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
