<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\IO\JSON;

trait JsonWrapper
{
    /**
     * Factory method for instantiating a DataFrame from a JSON string.
     */
    public static function fromJsonString(string $jsonString): self
    {
        $df = new self;
        JSON::importFromJsonString($df, $jsonString);

        return $df;
    }

    /**
     * Factory method for instantiating a DataFrame from a JSON file.
     */
    public static function fromJsonFile(string $jsonPath): self
    {
        $df = new self;
        JSON::importFromJsonFile($df, $jsonPath);

        return $df;
    }

    /**
     * Converts a DataFrame to a JSON string.
     */
    public function toJSON(bool $pretty = false): string
    {
        return JSON::encodeJSON($this->toArray(), $pretty);
    }
}
