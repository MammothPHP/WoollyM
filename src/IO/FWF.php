<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\DataFrame;

class FWF extends Builder
{
    /**
     * Colspecs
     */
    public array $colSpecs;

    /**
     * Whitelist regex to apply to each line of the file
     */
    public ?string $includeRegexOpt = null;

    /**
     * Blacklist regex to apply to each line of the file
     */
    public ?string $excludeRegexOpt = null;

    /**
     * Apply formats options
     */
    public function format(array $colSpecs): self
    {
        $this->colSpecs = $colSpecs;

        return $this;
    }

    /**
     * Apply filter options
     */
    public function filter(?string $includeRegexOpt = null, ?string $excludeRegexOpt = null): self
    {
        $this->includeRegexOpt = $includeRegexOpt;
        $this->excludeRegexOpt = $excludeRegexOpt;

        return $this;
    }

    public function import(DataFrame $to = new DataFrame): DataFrame
    {
        $data = $this->loadFile(
            fileData: $this->input ?? $this->convertSplFileToString()
        );

        return $to->addRecords($data);
    }

    /**
     * Loads the file which the FWF class was instantiated with.
     */
    protected function loadFile(string $fileData): array
    {
        $fileData = trim($fileData);
        $fileData = str_replace("\r", '', $fileData);
        $fileData = explode("\n", $fileData);
        $fileData = array_map('rtrim', $fileData);

        $fileData = $this->includeRegexOpt ? preg_grep($this->includeRegexOpt, $fileData) : $fileData;
        $fileData = $this->excludeRegexOpt ? preg_grep($this->excludeRegexOpt, $fileData, \PREG_GREP_INVERT) : $fileData;

        foreach ($fileData as &$line) {
            $line = $this->applyColSpecs($line, $this->colSpecs);
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
