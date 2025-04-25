<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use IteratorIterator;
use League\Csv\HTMLConverter;

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
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            libxml_use_internal_errors(true);
            $dom->loadHTML($r, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            $r = $dom->saveHTML();
        }

        return $r;
    }
}
