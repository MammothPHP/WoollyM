<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\XLSX;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait XlsxWrapper
{
    /**
     * Factory method for creating a DataFrame from an XLSX worksheet.
     */
    public static function fromXLSX(string $fileName, ?string $sheetName = null, int $colRow = 1): self
    {
        $xlsx = new XLSX($fileName);
        $data = $xlsx->loadFile(sheetName: $sheetName, colRow: $colRow);

        return new self($data);
    }

    /**
     * Output a DataFrame as a PHPExcel worksheet.
     */
    public function toXLSXWorksheet(Spreadsheet &$excel, string $worksheetTitle): Worksheet
    {
        return XLSX::saveToWorksheet($excel, $worksheetTitle, $this->toArray(), $this->columnsNames());
    }
}
