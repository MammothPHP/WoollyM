<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use Exception;
use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use Gajus\Dindent\Indenter;
use League\Csv\HTMLConverter;
use MammothPHP\WoollyM\DataFrame;

abstract class HTML
{
    public static function convertDataFrameToHtml(
        DataFrame $df,
        bool $pretty = true,
        ?int $limit = null,
        ?int $offset = 0,
        ?string $class = null,
        ?string $id = null
    ): string
    {
        $converter = (new HTMLConverter)->table($class ?? '', $id ?? '');

        $backupFillInNonExistentsCol = $df->fillInNonExistentsCol;
        $df->fillInNonExistentsCol = true;

        $iterable = $df->selectAll()->limit($limit)->offset($offset);

        try {
            $r = $converter->convert($iterable, $df->columnsNames(), $df->columnsNames());
        } catch (Exception $e) {
        } finally {
            $df->fillInNonExistentsCol = $backupFillInNonExistentsCol;
            ($e ?? null) instanceof Exception && throw $e;
        }

        return $pretty ? (new Indenter)->indent($r) : $r;
    }
}
