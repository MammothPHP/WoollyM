<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use League\Csv\{AbstractCsv, Reader, Writer};
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{FileExistsException, NotYetImplementedException};
use SplFileInfo;
use SplFileObject;

class CSV extends Builder
{
    public readonly DataFrame $fromDf;

    public readonly Reader $csvReader;
    public readonly mixed $ressource;

    public const string DEFAULT_DELIMITER = ',';
    public string $delimiter = self::DEFAULT_DELIMITER;

    public const string DEFAULT_ENCLOSURE = '"';
    public string $enclosure = self::DEFAULT_ENCLOSURE;

    public const string DEFAULT_ESCAPE = '\\';
    public string $escape = self::DEFAULT_ESCAPE;

    public const int|false DEFAULT_HEADER_OFFSET = 0;
    public int|false $headerOffset = self::DEFAULT_HEADER_OFFSET;

    public ?array $columns = null; // only if headeroffset is 0
    public array|false $onlyColumns = false;
    public ?array $mapping = null;

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
        ?string $delimiter = null,
        ?string $enclosure = null,
        ?string $escape = null,
        int|false|null $headerOffset = null,
        ?array $columns = null,
        ?array $mapping = null
    ): static {
        if ($delimiter) {
            $this->delimiter = $delimiter;
        }

        if ($enclosure) {
            $this->delimiter = $enclosure;
        }

        if ($escape) {
            $this->escape = $escape;
        }

        if ($headerOffset !== null) {
            $this->headerOffset = $headerOffset;
        }

        if ($columns) {
            $this->columns = $columns;
        }

        if ($mapping) {
            $this->mapping = $mapping;
        }

        return $this;
    }

    /**
     * Filter CSV input
     * @param $onlyColumns - Restrict import to theses columns, as column string name from header or $columns
     */
    public function filter(array|false|null $onlyColumns): static
    {
        if ($onlyColumns !== null) {
            $this->onlyColumns = $onlyColumns;
        }

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
            $offset = $this->headerOffset === false ? null : $this->headerOffset;
            $csv->setHeaderOffset($offset);
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
            if ($this->headerOffset === false && $this->columns !== null) {
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

    public static function fromDataFrame(DataFrame $df): static
    {
        $builder = new static;
        $builder->fromDf = $df;

        return $builder;
    }

    public function toFile(mixed $file, bool $overwriteFile = false, bool $writeHeader = true): void
    {
        if ($file instanceof SplFileInfo) {
            if (!$file instanceof SplFileObject) {
                $file = $file->openFile('w+');
            }

            $file = Writer::createFromFileObject($file);
        } elseif ($file instanceof Writer) {
            // Do nothing
        } elseif (\is_string($file)) {
            if (file_exists($file) && !$overwriteFile) {
                throw new FileExistsException("Write failed. File {$file} exists.");
            }

            $file = Writer::createFromPath($file, 'w+');
        } elseif (\is_resource($file)) {
            $file = Writer::createFromStream($file);
        } else {
            throw new NotYetImplementedException('Invalid File');
        }

        // Header
        $writeHeader && $file->insertOne($this->fromDf->columnsNames());

        // Records
        $previousParameter = $this->fromDf->fillInNonExistentsCol;
        $this->fromDf->fillInNonExistentsCol = true;

        $file->insertAll($this->fromDf);

        $this->fromDf->fillInNonExistentsCol = $previousParameter;
    }
}
