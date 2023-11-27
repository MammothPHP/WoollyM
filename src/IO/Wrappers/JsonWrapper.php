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
    public static function fromJSON($jsonString, array $options = []): self
    {
        $json = new JSON;
        $data = $json->decodeJSON($jsonString, $options);

        return new self($data);
    }

    /**
     * Converts a DataFrame to a JSON string.
     */
    public function toJSON(array $options = []): string
    {
        return (new JSON)->encodeJSON($this->toArray(), $options);
    }
}
