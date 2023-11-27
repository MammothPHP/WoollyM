<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\IO\Wrappers\{CsvWrapper, FwfWrapper, HtmlWrapper, JsonWrapper, SqlWrapper, XlsxWrapper};

class DataFrame extends DataFrameCore
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
