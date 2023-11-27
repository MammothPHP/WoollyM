<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use PhpOffice\PhpSpreadsheet\{IOFactory, Spreadsheet};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XLSX
{
    private $defaultOptions = [
        'colrow' => 1,
        'sheetname' => null,
    ];

    public function __construct(public readonly string $fileName) {}

    /**
     * Loads the file which the CSV class was instantiated with.
     * Options include:
     *      colrow:    The row of the spreadsheet which contains column data (default: 1)
     *      sheetname: The name of the worksheet to load. Defaults to first worksheet (default: null)
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function loadFile(array $options): array
    {
        $options = Options::setDefaultOptions($options, $this->defaultOptions);
        $colRowOpt = $options['colrow'];
        $sheetNameOpt = $options['sheetname'];

        $xlsx = IOFactory::load($this->fileName);

        if ($sheetNameOpt === null) {
            $sheet = $xlsx->getActiveSheet();
        } else {
            $sheet = $xlsx->getSheetByName($sheetNameOpt);
        }

        $columns = [];
        $data = [];

        $highestColumn = $sheet->getHighestColumn();
        $highestColumn++;

        foreach ($sheet->getRowIterator($colRowOpt) as $i => $row) {
            for ($column = 'A'; $column != $highestColumn; $column++) {
                /*
                 * If the current row is the column row then assemble our columns.
                 */
                if ($i === $colRowOpt) {
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
