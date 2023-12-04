<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use League\Csv\{AbstractCsv, Reader, Writer};
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{FileExistsException, NotYetImplementedException};
use SplFileInfo;
use SplFileObject;

class CSV
{
    public const string DEFAULT_DELIMITER = ',';
    public string $delimiter = self::DEFAULT_DELIMITER;

    public const string DEFAULT_ENCLOSURE = '"';
    public string $enclosure = self::DEFAULT_ENCLOSURE;

    public const string DEFAULT_ESCAPE = '\\';
    public string $escape = self::DEFAULT_ESCAPE;

    public const ?int DEFAULT_HEADER_OFFSET = 0;
    public ?int $headerOffset = self::DEFAULT_HEADER_OFFSET;

    public ?array $columns = null; // only if headeroffset is null
    public ?array $onlyColumns = null;
    public ?array $mapping = null;

    public function __construct(public readonly DataFrame $df) {}

    protected function applyOptions(AbstractCsv $csv): void
    {
        $csv->setDelimiter($this->delimiter);
        $csv->setEnclosure($this->enclosure);
        $csv->setEscape($this->escape);
        $csv instanceof Reader && $csv->setHeaderOffset($this->headerOffset);
    }

    public function importFrom(mixed $input): DataFrame
    {
        if ($input instanceof SplFileInfo) {
            return self::importFromFileObject($input);
        } elseif ($input instanceof Reader) {
            return self::importFromCsvReader($input);
        } elseif (\is_string($input)) {
            return (file_exists($input)) ? self::importFromPath($input) : self::importFromString($input);
        } elseif (\is_resource($input)) {
            return self::importFromStream($input);
        } else {
            throw new NotYetImplementedException('Invalid Input');
        }
    }

    public function importFromFileObject(SplFileInfo $file): DataFrame
    {
        if (!$file instanceof SplFileObject) {
            $file = $file->openFile('r');
        }

        $reader = Reader::createFromFileObject($file);

        if (!($file->getFlags() & SplFileObject::READ_CSV)) {
            self::applyOptions($reader);
        }

        return self::importFromCsvReader($reader);
    }

    public function importFromPath(string $file): DataFrame
    {
        $reader = Reader::createFromPath($file);
        self::applyOptions($reader);

        return self::importFromCsvReader($reader);
    }

    public function importFromString(string $input): DataFrame
    {
        $reader = Reader::createFromString($input);
        self::applyOptions($reader);

        return self::importFromCsvReader($reader);
    }

    public function importFromStream($stream): DataFrame
    {
        $reader = Reader::createFromStream($stream);
        self::applyOptions($reader);

        return self::importFromCsvReader($reader);
    }

    public function importFromCsvReader(Reader $reader): DataFrame
    {
        foreach ($reader->getRecords() as $record) {
            $newRecord = [];

            if ($this->mapping !== null || $this->onlyColumns !== null) {
                foreach ($record as $k => $v) {
                    if ($this->onlyColumns !== null && !\in_array($k, $this->onlyColumns, true)) {
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

            $this->df->addRecord($newRecord);
        }

        return $this->df;
    }

    public function saveToFile(mixed $file, bool $overwrite = false, bool $writeHeader = true): void
    {
        if ($file instanceof SplFileInfo) {
            if (!$file instanceof SplFileObject) {
                $file = $file->openFile('w+');
            }

            $file = Writer::createFromFileObject($file);
        } elseif ($file instanceof Writer) {
            // Do nothing
        } elseif (\is_string($file)) {
            if (file_exists($file) && !$overwrite) {
                throw new FileExistsException("Write failed. File {$file} exists.");
            }

            $file = Writer::createFromPath($file, 'w+');
        } elseif (\is_resource($file)) {
            $file = Writer::createFromStream($file);
        } else {
            throw new NotYetImplementedException('Invalid File');
        }

        // Header
        $writeHeader && $file->insertOne($this->df->columnsNames());

        // Records
        $previousParameter = $this->df->fillInNonExistentsCol;
        $this->df->fillInNonExistentsCol = true;

        $file->insertAll($this->df);

        $this->df->fillInNonExistentsCol = $previousParameter;
    }
}
