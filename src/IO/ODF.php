<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Ods as WriterOds;

class ODF extends XLSX
{
    protected function getWriter(Spreadsheet $spreadsheet): WriterOds
    {
        return new WriterOds($spreadsheet);
    }
}