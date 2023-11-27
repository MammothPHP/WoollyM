<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\IO\FWF;

trait FwfWrapper
{
    /**
     * Factory method for creating a DataFrame from a fixed-width file.
     */
    public static function fromFWF(string $fileName, array $colSpecs, array $options = []): self
    {
        $fwf = new FWF($fileName);
        $data = $fwf->loadFile($colSpecs, $options);

        return new self($data);
    }
}
