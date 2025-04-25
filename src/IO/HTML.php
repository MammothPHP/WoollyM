<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use IteratorIterator;
use League\Csv\HTMLConverter;
use tidy;

class HTML
{
    use BuilderExport;

    public function toString(
        bool $pretty = true,
        ?int $limit = null,
        ?int $offset = 0,
        ?string $class = null,
        ?string $id = null
    ): string {
        if ($limit !== null && $limit < 1) {
            throw new \ValueError('$limit can\'t be less than 1');
        }
        elseif ($offset !== null && $offset < 0) {
            throw new \ValueError('$offset can\'t be less than 0');
        }


        $converter = new HTMLConverter()->table($class ?? '', $id ?? '');

        $iterable = $this->fromDf->selectAll()->limit($limit)->offset($offset);

        $transformer = new class ($iterable) extends IteratorIterator {
            public function current(): array
            {
                $row = parent::current();

                foreach ($row as $key => $value) {
                    if (!is_string($value)) {
                        $row[$key] = (string) $value;
                    }
                }

                return $row;
            }
        };

        $r = $converter->convert($transformer, $this->fromDf->columnsNames(), $this->fromDf->columnsNames());

        if ($pretty) {
            $tidy = new tidy;
            $tidy->parseString($r, ['indent' => true, 'newline' => "\n"], 'utf8');
            $tidy->cleanRepair();

            $r = tidy_get_output($tidy);
        }

        return $r;
    }
}
