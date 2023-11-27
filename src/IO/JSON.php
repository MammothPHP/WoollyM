<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;

class JSON
{
    private $defaultOptions = [
        'pretty' => false,
    ];

    /**
     * Encodes a DataFrame array into a JSON string.
     *      pretty: Will "prettify" the rendered JSON (default: false)
     * @throws NotYetImplementedException
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public function encodeJSON(array $data, array $options): string
    {
        $options = Options::setDefaultOptions($options, $this->defaultOptions);

        $prettyOpt = $options['pretty'];
        if ($prettyOpt === true) {
            $prettyOpt = \JSON_PRETTY_PRINT;
        }

        return json_encode($data, (int) $prettyOpt);
    }

    /**
     * Decodes a JSON string into a DataFrame array.
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public function decodeJSON(string $jsonString, array $options): mixed
    {
        Options::setDefaultOptions($options, $this->defaultOptions);

        return json_decode(json: $jsonString, associative: true, flags: \JSON_THROW_ON_ERROR);
    }
}
