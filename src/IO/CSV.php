<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use League\Csv\{AbstractCsv, CannotInsertRecord, Exception, InvalidArgument, Reader, Writer};
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{FileExistsException, NotYetImplementedException};
use SplFileInfo;
use SplFileObject;

class CSV extends Builder
{
    use BuilderExport;

    public readonly Reader $csvReader;
    public readonly mixed $ressource;

    public const string DEFAULT_DELIMITER = ',';
    public string $delimiter = self::DEFAULT_DELIMITER;

    public const string DEFAULT_ENCLOSURE = '"';
    public string $enclosure = self::DEFAULT_ENCLOSURE;

    public const string DEFAULT_ESCAPE = '\\';
    public string $escape = self::DEFAULT_ESCAPE;

    public const ?int DEFAULT_HEADER_OFFSET = 0;
    public ?int $headerOffset = self::DEFAULT_HEADER_OFFSET;

    public const ?array DEFAULT_COLUMNS = null;
    public ?array $columns = self::DEFAULT_COLUMNS; // only if headeroffset is null

    public const array|false DEFAULT_ONLY_COLUMNS = false;
    public array|false $onlyColumns = self::DEFAULT_ONLY_COLUMNS;

    public const ?array DEFAULT_MAPPING = null;
    public ?array $mapping = self::DEFAULT_MAPPING;

    public static function fromCsvReader(Reader $csvReader): static
    {
        $builder = new static;
        $builder->csvReader = $csvReader;

        return $builder;
    }

    public static function fromStream($stream): static
    {
        $builder = new static;
        $builder->ressource = $stream;

        return $builder;
    }

    /**
     * Format options. If a parameter is null, default value or set previous parameter set for object will be applied.
     * @param $headerOffset - To be 0 if no header line is present, else number of lines to ignore
     * @param $columns - Ordered array of columns, only if $headerOffset is false
     * @param $mapping - Change a colonne name to another
     */
    public function format(
        string $delimiter = self::DEFAULT_DELIMITER,
        string $enclosure = self::DEFAULT_ENCLOSURE,
        string $escape = self::DEFAULT_ESCAPE,
        ?int $headerOffset = self::DEFAULT_HEADER_OFFSET,
        ?array $columns = self::DEFAULT_COLUMNS,
        ?array $mapping = self::DEFAULT_MAPPING
    ): static {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->headerOffset = $headerOffset;
        $this->columns = $columns;
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * Filter CSV input
     * @param $onlyColumns - Restrict import to theses columns, as column string name from header or $columns
     */
    public function filter(array|false $onlyColumns): static
    {
        $this->onlyColumns = $onlyColumns;

        return $this;
    }

    public function import(DataFrame $to = new DataFrame): DataFrame
    {
        $applyOptions = true;

        if ($this->file ?? false) {
            self::ReaderFromFileObject($applyOptions);
        } elseif ($this->input ?? false) {
            self::ReaderFromString();
        } elseif ($this->ressource ?? false) {
            self::ReaderFromStream();
        }

        if (!($this->csvReader ?? false)) {
            throw new NotYetImplementedException('Invalid Input');
        }

        $applyOptions && self::applyOptions($this->csvReader);

        return $this->importFromCsvReader($to);
    }

    protected function applyOptions(AbstractCsv $csv): void
    {
        $csv->setDelimiter($this->delimiter);
        $csv->setEnclosure($this->enclosure);
        $csv->setEscape($this->escape);

        if ($csv instanceof Reader) {
            $csv->setHeaderOffset($this->headerOffset);
        }
    }

    protected function ReaderFromFileObject(bool &$applyOptions): void
    {
        if (!$this->file instanceof SplFileObject) {
            $file = $this->file->openFile('r');
        }

        $this->csvReader = Reader::createFromFileObject($file);

        if (($file->getFlags() & SplFileObject::READ_CSV)) {
            $applyOptions = false;
        }
    }

    protected function ReaderFromString(): void
    {
        $this->csvReader = Reader::createFromString($this->input);
    }

    protected function ReaderFromStream(): void
    {
        $this->csvReader = Reader::createFromStream($this->ressource);
    }

    protected function importFromCsvReader(DataFrame $to): DataFrame
    {
        foreach ($this->csvReader->getRecords() as $record) {
            $newRecord = [];

            if ($this->mapping !== null || $this->onlyColumns !== false) {
                foreach ($record as $k => $v) {
                    if ($this->onlyColumns !== false && !\in_array($k, $this->onlyColumns, true)) {
                        continue;
                    }

                    if (\array_key_exists($k, $this->mapping)) {
                        if (!\is_string($this->mapping[$k]) || trim($this->mapping[$k]) === '') {
                            continue;
                        }

                        $newRecord[$this->mapping[$k]] = $v;
                    } else {
                        $newRecord[$k] = $v;
                    }
                }
            } else {
                $newRecord = $record;
            }

            // Columns Renaming (mapping)
            if ($this->headerOffset === null && $this->columns !== null) {
                foreach ($newRecord as $k => $v) {
                    if (\array_key_exists($k, $this->columns) && \is_string($this->columns[$k]) && trim($this->columns[$k]) !== '') {
                        $newRecord[$this->columns[$k]] = $v;
                        unset($newRecord[$k]);
                    }
                }
            }

            $to->addRecord($newRecord);
        }

        return $to;
    }

    /**
     * Write CSV from dataFrame to a file
     */
    public function toFile(string|SplFileInfo|Writer $file, bool $overwriteFile = false, bool $writeHeader = true): void
    {
        if ($file instanceof Writer) {
            $writer = $file;
        } elseif ($convertedFile = $this->prepareToFileInput($file, $overwriteFile)) {
            $writer = Writer::createFromFileObject($convertedFile);
        } else {
            throw new NotYetImplementedException('Invalid File');
        }

        $this->writeCsv($writer, $writeHeader);
    }

    /**
     * Write CSV from dataFrame to a stream resource
     */
    public function toStream($phpStream, bool $writeHeader = true): void
    {
        if (\is_resource($phpStream)) {
            $writer = Writer::createFromStream($phpStream);
        } else {
            throw new NotYetImplementedException('Invalid Stream');
        }

        $this->writeCsv($writer, $writeHeader);
    }

    /**
     * Write CSV from dataFrame to a string
     */
    public function toString(bool $writeHeader = true): string
    {
        $writer = Writer::createFromString();
        $this->writeCsv($writer, $writeHeader);

        return $writer->toString();
    }

    protected function writeCsv(Writer $writer, bool $writeHeader): void
    {
        // Options
        $this->applyOptions($writer);

        // Header
        $writeHeader && $writer->insertOne($this->fromDf->columnsNames());

        // Records
        $previousParameter = $this->fromDf->fillInNonExistentsCol;
        $this->fromDf->fillInNonExistentsCol = true;

        $writer->insertAll($this->fromDf);

        $this->fromDf->fillInNonExistentsCol = $previousParameter;
    }
}
