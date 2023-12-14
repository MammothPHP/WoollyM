<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use League\Csv\{AbstractCsv, Reader, Writer};
use MammothPHP\WoollyM\Exceptions\{FileExistsException, NotYetImplementedException};

class TSV extends CSV
{
    public string $delimiter = "\t";
}
