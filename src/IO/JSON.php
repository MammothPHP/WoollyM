<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;

abstract class JSON
{
    /**
     * Encodes a DataFrame array into a JSON string.
     *      pretty: Will "prettify" the rendered JSON (default: false)
     * @throws NotYetImplementedException
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public static function encodeJSON(array $data, bool $pretty = false): string
    {
        return json_encode($data, $pretty ? \JSON_PRETTY_PRINT : 0);
    }

    /**
     * Decodes a JSON string into a DataFrame array.
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     */
    public static function importFromJsonFile(DataFrame $df, string $jsonFile): void
    {
        $jsonItems = Items::fromFile($jsonFile, ['decoder' => new ExtJsonDecoder(true)]);
        self::importFromJsonItemsIterable($df, $jsonItems);
    }

    public static function importFromJsonString(DataFrame $df, string $jsonFile): void
    {
        $jsonItems = Items::fromString($jsonFile, ['decoder' => new ExtJsonDecoder(true)]);
        self::importFromJsonItemsIterable($df, $jsonItems);
    }

    public static function importFromJsonItemsIterable(DataFrame $df, Items $jsonItemsIterable): void
    {
        foreach ($jsonItemsIterable as $record) {
            $df->addRecord($record);
        }
    }
}
