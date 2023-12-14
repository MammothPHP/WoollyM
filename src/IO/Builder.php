<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use SplFileInfo;

abstract class Builder
{
    public readonly SplFileInfo $file;
    public readonly string $input;

    public static function fromFilePath(string $path): static
    {
        $file = new SplFileInfo($path);

        return self::fromFileInfo($file);
    }

    public static function fromFileInfo(SplFileInfo $file): static
    {
        if (!$file->isFile() || !$file->isReadable()) {
            return throw new NotYetImplementedException('Invalid Input');
        }

        $builder = new static;
        $builder->file = $file;

        return $builder;
    }

    public static function fromString(string $input): static
    {
        $builder = new static;
        $builder->input = $input;

        return $builder;
    }

    protected function convertSplFileToString(): string
    {
        return file_get_contents($this->file->getPathname());
    }

    abstract public function import(DataFrame $to = new DataFrame): DataFrame;

    public function toArray(): array
    {
        return $this->import()->toArray();
    }
}
