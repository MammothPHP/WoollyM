<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use PhpOffice\PhpSpreadsheet\{IOFactory, Spreadsheet};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XLSX
{
    public function __construct(public readonly string $fileName) {}

    /**
     * Loads the file which the CSV class was instantiated with.
     * @param $sheetname - The name of the worksheet to load. Defaults to first worksheet (default: null)
     * @param $colRow - The row of the spreadsheet which contains column data (default: 1)
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function loadFile(?string $sheetName = null, int $colRow = 1): array
    {
        $xlsx = IOFactory::load($this->fileName);

        if ($sheetName === null) {
            $sheet = $xlsx->getActiveSheet();
        } else {
            $sheet = $xlsx->getSheetByName($sheetName);
        }

        $columns = [];
        $data = [];

        $highestColumn = $sheet->getHighestColumn();
        $highestColumn++;

        foreach ($sheet->getRowIterator($colRow) as $i => $row) {
            for ($column = 'A'; $column != $highestColumn; $column++) {
                /*
                 * If the current row is the column row then assemble our columns.
                 */
                if ($i === $colRow) {
                    $columns[$column] = $sheet->getCell($column . $i)->__toString();

                    continue;
                }

                $currentColumnName = $columns[$column];
                $data[$i][$currentColumnName] = $sheet->getCell($column . $i)->__toString();
            }
        }

        return array_values($data);
    }

    /**
     * Converts the columns and data passed to an XLSX worksheet and adds that worksheet to an instance of PHPExcel
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function saveToWorksheet(Spreadsheet $excel, string $worksheetTitle, array $data, array $columns): Worksheet
    {
        // Check if this is a brand new spreadsheet
        if ($excel->getSheetCount() === 1) {
            $sheet = $excel->getActiveSheet();
            $sheetName = $sheet->getCodeName();

            $colCount = $sheet->getHighestColumn();
            $rowCount = $sheet->getHighestRow();

            $cell = $sheet->getCell('A1')->getValue();

            // If this is a brand new spreadsheet then remove the first worksheet
            if ($sheetName === 'Worksheet' && $colCount === 'A' && $rowCount === 1 && $cell === null) {
                $excel->removeSheetByIndex(0);
            }
        }

        $worksheet = new Worksheet($excel, $worksheetTitle);

        $wsArray = [$columns];
        foreach ($data as $row) {
            $wsArray[] = array_values($row);
        }

        $worksheet->fromArray($wsArray, null, 'A1', false);
        $excel->addSheet($worksheet);

        return $worksheet;
    }
}
