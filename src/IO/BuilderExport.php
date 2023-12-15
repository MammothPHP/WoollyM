<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\FileExistsException;
use SplFileInfo;
use SplFileObject;

trait BuilderExport
{
    public readonly DataFrame $fromDf;

    public static function fromDataFrame(DataFrame $df): static
    {
        $builder = new static;
        $builder->fromDf = $df;

        return $builder;
    }

    protected function prepareToFileInput(mixed $file, bool $overwriteFile): SplFileObject|false
    {
        if ($file instanceof SplFileInfo) {
            if (!$file instanceof SplFileObject) {
                $file = $file->openFile('w+');
            }

            return $file;
        } elseif (\is_string($file)) {
            if (file_exists($file) && !$overwriteFile) {
                throw new FileExistsException("Write failed. File {$file} exists.");
            }

            return new SplFileObject($file, 'w+');
        }

        return false;
    }
}
