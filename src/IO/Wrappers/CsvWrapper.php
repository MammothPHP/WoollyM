<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\CSV;

trait CsvWrapper
{
    /**
     * Factory method for creating a DataFrame from a CSV file.
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
     * @throws \MammothPHP\WoollyM\Exceptions\FileExistsException
     */
    public function toCSV(
        mixed $file,
        bool $overwrite = false,
        bool $writeHeader = true,
        string $delimiter = CSV::DEFAULT_DELIMITER,
        string $enclosure = CSV::DEFAULT_ENCLOSURE,
        string $escape = CSV::DEFAULT_ESCAPE,
    ): self {
        $csv = new CSV($this);

        $csv->delimiter = $delimiter;
        $csv->enclosure = $enclosure;
        $csv->escape = $escape;

        $csv->saveToFile(file: $file, overwrite: $overwrite, writeHeader: $writeHeader);

        return $this;
    }
}
