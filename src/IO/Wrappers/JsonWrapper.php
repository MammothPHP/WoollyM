<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\JSON;

trait JsonWrapper
{
    /**
     * Factory method for instantiating a DataFrame from a JSON string.
     */
    public static function fromJSON($jsonString): self
    {
        $data = JSON::decodeJSON($jsonString);

        return new self($data);
    }

    /**
     * Converts a DataFrame to a JSON string.
     */
    public function toJSON(bool $pretty = false): string
    {
        return JSON::encodeJSON($this->toArray(), $pretty);
    }
}
