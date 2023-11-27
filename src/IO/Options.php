<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\Exceptions\UnknownOptionException;

class Options
{
    /**
     * Will apply all default options to an associative array of user-provided options.
     * @throws UnknownOptionException Exception when an option is unknown.
     */
    public static function setDefaultOptions(array $userOptions, array $defaultOptions): array
    {
        /*
         * First, override all default options with whatever ones have been
         * user-specified.
         */

        $unknownOptions = [];
        foreach ($userOptions as $optionName => $optionValue) {
            // Check if user provided any invalid options.
            if (\array_key_exists($optionName, $defaultOptions) === false) {
                $unknownOptions[] = $optionName;

                continue;
            } else {
                // Otherwise override the default value for that option.
                $defaultOptions[$optionName] = $optionValue;
            }
        }

        if (\count($unknownOptions) > 0) {
            $unknownOptions = implode(', ', $unknownOptions);

            throw new UnknownOptionException('Unknown options: [' . $unknownOptions . ']');
        }

        /*
         * Then once the default options have been overridden, populate the
         * user-provided options array with them and pass it back to be used.
         */
        foreach ($defaultOptions as $optionName => $optionValue) {
            /* This will add all our default option values to the user provided
             * array.
             */
            if (\array_key_exists($optionName, $userOptions) === false) {
                $userOptions[$optionName] = $optionValue;
            }
        }

        return $userOptions;
    }
}
