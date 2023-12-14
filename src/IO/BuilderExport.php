<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\DataFrame;

trait BuilderExport
{
    public readonly DataFrame $fromDf;

    public static function fromDataFrame(DataFrame $df): static
    {
        $builder = new static;
        $builder->fromDf = $df;

        return $builder;
    }
}