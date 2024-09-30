<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use PhpOffice\PhpSpreadsheet\{IOFactory, Spreadsheet};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\{BaseWriter, Xlsx as WriterXlsx};
use SplFileInfo;

class XLSX extends Builder
{
    use BuilderExport;

    public const ?string DEFAULT_SHEET_NAME = null;
    public ?string $sheetName = self::DEFAULT_SHEET_NAME;

    public const int DEFAULT_COLROW = 1;
    public int $colRow = self::DEFAULT_COLROW;

    public function import(DataFrame $to = new DataFrame): DataFrame
    {
        $fileName = $this->file?->getPathname() ?? $this->input ?? false;

        if ($fileName === false) {
            throw new NotYetImplementedException('Invalid file');
        }

        $data = $this->loadFile($fileName);

        return new DataFrame($data);
    }

    /**
     * @param $colRow - Parse data after specified line (starting at 1), and consider this line at the header. Set to 0 for no header.
     */
    public function format(?string $sheetName = self::DEFAULT_SHEET_NAME, int $colRow = self::DEFAULT_COLROW): static
    {
        $this->sheetName = $sheetName;
        $this->colRow = $colRow;

        return $this;
    }

    /**
     * Loads the file which the CSV class was instantiated with.
     * @throws \MammothPHP\WoollyM\Exceptions\UnknownOptionException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function loadFile(string $file): array
    {
        $xlsx = IOFactory::load($file);

        if ($this->sheetName === null) {
            $sheet = $xlsx->getActiveSheet();
        } else {
            $sheet = $xlsx->getSheetByName($this->sheetName);
        }

        $columns = [];
        $data = [];

        $highestColumn = $sheet->getHighestColumn();
        $highestColumn++;

        foreach ($sheet->getRowIterator($this->colRow) as $i => $row) {
            for ($column = 'A'; $column != $highestColumn; $column++) {
                /*
                 * If the current row is the column row then assemble our columns.
                 */
                if ($i < $this->colRow) {
                    continue;
                } elseif ($i === $this->colRow) {
                    $columns[$column] = $sheet->getCell($column . $i)->__toString();

                    continue;
                }

                $currentColumnName = $columns[$column];
                $data[$i][$currentColumnName] = $sheet->getCell($column . $i)->__toString();
            }
        }

        return array_values($data);
    }

    protected function getWriter(Spreadsheet $spreadsheet): BaseWriter
    {
        return new WriterXlsx($spreadsheet);
    }

    /**
     * Write an Excel file
     */
    public function toFile(string|SplFileInfo $filePath, bool $overwriteFile = false, string $worksheetTitle = 'DataFrame'): void
    {
        if ($convertedFile = $this->prepareToFileInput($filePath, $overwriteFile)) {
            $spreadsheet = new Spreadsheet;
            $this->toExcelSpreadsheet(spreadsheet: $spreadsheet, worksheetTitle: $worksheetTitle);
        } else {
            throw new NotYetImplementedException('Invalid file');
        }

        $writer = $this->getWriter($spreadsheet);
        $writer->save($convertedFile->getPathname());
    }

    /**
     * Converts the columns and data passed to an XLSX worksheet and adds that worksheet to an instance of PHPExcel
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function toExcelSpreadsheet(Spreadsheet $spreadsheet, string $worksheetTitle = 'DataFrame'): Worksheet
    {
        // Check if this is a brand new spreadsheet
        if ($spreadsheet->getSheetCount() === 1) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheetName = $sheet->getCodeName();

            $colCount = $sheet->getHighestColumn();
            $rowCount = $sheet->getHighestRow();

            $cell = $sheet->getCell('A1')->getValue();

            // If this is a brand new spreadsheet then remove the first worksheet
            if ($sheetName === 'Worksheet' && $colCount === 'A' && $rowCount === 1 && $cell === null) {
                $spreadsheet->removeSheetByIndex(0);
            }
        }

        $worksheet = new Worksheet($spreadsheet, $worksheetTitle);

        $wsArray = [$this->fromDf->columnsNames()];
        foreach ($this->fromDf->selectAll() as $line) {
            foreach ($line as &$cell) {
                if (\is_array($cell) || \is_object($cell)) {
                    $cell = json_encode($cell, \JSON_PRETTY_PRINT);
                }
            }

            $wsArray[] = $line;
        }

        $spreadsheet->addSheet($worksheet);
        $worksheet->fromArray($wsArray, null, 'A1', false);

        return $worksheet;
    }
}
