<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;

class JSON extends Builder
{
    use BuilderExport;

    public readonly Items $jsonItems;

    public function import(DataFrame $to = new DataFrame): DataFrame
    {
        if ($this->file ?? false) {
            $this->itemsFromFile();
        } elseif ($this->input ?? false) {
            $this->itemsFromString();
        }

        if (!($this->jsonItems ?? false)) {
            throw new NotYetImplementedException('Invalid Input');
        }

        $this->importFromJsonItems($to);

        return $to;
    }


    /**
     * Encodes a DataFrame array into a JSON string.
     *      pretty: Will "prettify" the rendered JSON (default: false)
     * @throws NotYetImplementedException
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public function toString(bool $pretty = false): string
    {
        return json_encode($this->fromDf->toArray(), $pretty ? \JSON_PRETTY_PRINT : 0);
    }

    /**
     * Decodes a JSON string into a DataFrame array.
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    protected function itemsFromFile(): void
    {
        $this->jsonItems = Items::fromFile($this->file->getPathname(), ['decoder' => new ExtJsonDecoder(true)]);
    }

    public function itemsFromString(): void
    {
        $this->jsonItems = Items::fromString($this->input, ['decoder' => new ExtJsonDecoder(true)]);
    }

    protected function importFromJsonItems(DataFrame $df): void
    {
        foreach ($this->jsonItems as $record) {
            $df->addRecord($record);
        }
    }
}
