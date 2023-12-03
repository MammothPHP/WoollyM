<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\IO\FWF;

trait FwfWrapper
{
    /**
     * Factory method for creating a DataFrame from a fixed-width file.
     */
    public static function fromFWF(string $fileName, array $colSpecs, ?string $includeRegexOpt = null, ?string $excludeRegexOpt = null): self
    {
        $fwf = new FWF($fileName);
        $data = $fwf->loadFile(colSpecs: $colSpecs, includeRegexOpt: $includeRegexOpt, excludeRegexOpt: $excludeRegexOpt);

        return new self($data);
    }
}
