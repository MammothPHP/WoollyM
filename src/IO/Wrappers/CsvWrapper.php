<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\CSV;

trait CsvWrapper
{
    /**
     * Factory method for creating a DataFrame from a CSV file.
     * @param SplFileInfo|Reader|string|resource $input representing a file (or a file path)
     * @param $headerOffset - To be null if no header line is present, else number of lines to ignore
     * @param $columns - Ordered array of columns, only if $headerOffset is null
     * @param $onlyColumns - Restrict import to theses columns, as column string name from header of $columns
     * @param $mapping - Change a colonne name to another
     */
    public static function fromCSV(
        mixed $input,
        string $delimiter = CSV::DEFAULT_DELIMITER,
        string $enclosure = CSV::DEFAULT_ENCLOSURE,
        string $escape = CSV::DEFAULT_ESCAPE,
        ?int $headerOffset = CSV::DEFAULT_HEADER_OFFSET,
        ?array $columns = null,
        ?array $onlyColumns = null,
        ?array $mapping = null,
    ): DataFrame {
        $csv = new CSV(new self);

        $csv->delimiter = $delimiter;
        $csv->enclosure = $enclosure;
        $csv->escape = $escape;
        $csv->headerOffset = $headerOffset;
        $csv->columns = $columns;
        $csv->onlyColumns = $onlyColumns;
        $csv->mapping = $mapping;

        return $csv->importFrom($input);
    }

    /**
     * Factory method for creating a DataFrame from a TSV file.
     * @param SplFileInfo|Reader|string|resource $input representing a file (or a file path)
     * @param $headerOffset - To be null if no header line is present, else number of lines to ignore
     * @param $columns - Ordered array of columns, only if $headerOffset is null
     * @param $onlyColumns - Restrict import to theses columns, as column string name from header of $columns
     * @param $mapping - Change a colonne name to another
     */
    public static function fromTSV(
        mixed $input,
        string $enclosure = CSV::DEFAULT_ENCLOSURE,
        string $escape = CSV::DEFAULT_ESCAPE,
        ?int $headerOffset = CSV::DEFAULT_HEADER_OFFSET,
        ?array $columns = null,
        ?array $onlyColumns = null,
        ?array $mapping = null,
    ): DataFrame {
        return self::fromCSV(
            input: $input,
            enclosure: $enclosure,
            delimiter: "\t",
            escape: $escape,
            headerOffset: $headerOffset,
            columns: $columns,
            onlyColumns: $onlyColumns,
            mapping: $mapping
        );
    }

    /**
     * Outputs a DataFrame to a CSV file.
     * @param SplFileInfo|Reader|string|resource $file Representing a file (or a file path)
     * @throws \MammothPHP\WoollyM\Exceptions\FileExistsException
     */
    public function toCSV(
        mixed $file,
        bool $overwriteFile = false,
        bool $writeHeader = true,
        string $delimiter = CSV::DEFAULT_DELIMITER,
        string $enclosure = CSV::DEFAULT_ENCLOSURE,
        string $escape = CSV::DEFAULT_ESCAPE,
    ): self {
        $csv = new CSV($this);

        $csv->delimiter = $delimiter;
        $csv->enclosure = $enclosure;
        $csv->escape = $escape;

        $csv->saveToFile(file: $file, overwriteFile: $overwriteFile, writeHeader: $writeHeader);

        return $this;
    }
}
