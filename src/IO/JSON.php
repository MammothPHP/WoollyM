<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;

abstract class JSON
{
    /**
     * Encodes a DataFrame array into a JSON string.
     *      pretty: Will "prettify" the rendered JSON (default: false)
     * @throws NotYetImplementedException
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public static function encodeJSON(array $data, bool $pretty = false): string
    {
        return json_encode($data, $pretty ? \JSON_PRETTY_PRINT : 0);
    }

    /**
     * Decodes a JSON string into a DataFrame array.
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public static function decodeJSON(string $jsonString): mixed
    {
        return json_decode(json: $jsonString, associative: true, flags: \JSON_THROW_ON_ERROR);
    }
}
