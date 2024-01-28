<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use Exception;
use League\Csv\HTMLConverter;
use tidy;

class HTML
{
    use BuilderExport;

    public bool $pretty;
    public ?int $limit;
    public ?int $offset;
    public ?string $class;
    public ?string $id;

    public function toString(
        bool $pretty = true,
        ?int $limit = null,
        ?int $offset = 0,
        ?string $class = null,
        ?string $id = null
    ): string {
        $converter = (new HTMLConverter)->table($class ?? '', $id ?? '');

        $backupFillInNonExistentsCol = $this->fromDf->fillInNonExistentsCol;
        $this->fromDf->fillInNonExistentsCol = true;

        $iterable = $this->fromDf->selectAll()->limit($limit)->offset($offset);

        try {
            $r = $converter->convert($iterable, $this->fromDf->columnsNames(), $this->fromDf->columnsNames());
        } catch (Exception $e) {
        } finally {
            $this->fromDf->fillInNonExistentsCol = $backupFillInNonExistentsCol;
            ($e ?? null) instanceof Exception && throw $e;
        }

        if ($pretty) {
            $tidy = new tidy;
            $tidy->parseString($r, ['indent' => true], 'utf8');
            $tidy->cleanRepair();

            $r = tidy_get_output($tidy);
        }

        return $r;
    }
}
