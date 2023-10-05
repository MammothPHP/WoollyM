<?php

declare(strict_types=1);

/**
 * Contains the JSON class.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/Archon/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/Archon
 * @since     0.4.0
 */

namespace Archon\IO;

use Archon\Exceptions\NotYetImplementedException;

/**
 * The JSON class contains implementation details for encoding and decoding a DataFrame into and from a JSON string.
 * @package   Archon\IO
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/Archon/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/Archon
 * @since     0.4.0
 */
final class JSON
{
    private $defaultOptions = [
        'pretty' => false,
    ];

    /**
     * Encodes a DataFrame array into a JSON string.
     *      pretty: Will "prettify" the rendered JSON (default: false)
     * @param  array $data
     * @param  array $options
     * @return array|string
     * @throws NotYetImplementedException
     * @throws \Archon\Exceptions\UnknownOptionException
     * @since  0.4.0
     */
    public function encodeJSON(array $data, array $options)
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
     * @param  $jsonString
     * @param  array $options
     * @return mixed
     * @throws \Archon\Exceptions\UnknownOptionException
     * @since  0.4.0
     */
    public function decodeJSON($jsonString, array $options)
    {
        Options::setDefaultOptions($options, $this->defaultOptions);

        return json_decode($jsonString, true);
    }
}
