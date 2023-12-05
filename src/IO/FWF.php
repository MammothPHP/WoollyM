<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

class FWF
{
    public function __construct(public readonly string $fileName) {}

    /**
     * Loads the file which the FWF class was instantiated with.
     * @param $includeRegexOpt - Whitelist regex to apply to each line of the file (default: null)
     * @param $excludeRegexOpt - Blacklist regex to apply to each line of the file (default: null)
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public function loadFile(array $colSpecs, ?string $includeRegexOpt = null, ?string $excludeRegexOpt = null): array
    {
        $fileName = $this->fileName;

        $fileData = file_get_contents($fileName);
        $fileData = trim($fileData);
        $fileData = str_replace("\r", '', $fileData);
        $fileData = explode("\n", $fileData);
        $fileData = array_map('rtrim', $fileData);

        $fileData = $includeRegexOpt ? preg_grep($includeRegexOpt, $fileData) : $fileData;
        $fileData = $excludeRegexOpt ? preg_grep($excludeRegexOpt, $fileData, \PREG_GREP_INVERT) : $fileData;

        foreach ($fileData as &$line) {
            $line = $this->applyColSpecs($line, $colSpecs);
        }

        return array_values($fileData);
    }

    /**
     * Parses a string of data based on the rules defined in user provided colspecs.
     */
    private function applyColSpecs(string $data, array $colSpecs): array
    {
        $result = [];

        foreach ($colSpecs as $colName => $coords) {
            if ($coords[0] === '*') {
                $coords[0] = 0;
            }

            if ($coords[1] === '*') {
                $coords[1] = \strlen($data);
            }

            $result[$colName] = trim(substr($data, $coords[0], $coords[1] - $coords[0]));
        }

        return $result;
    }
}
