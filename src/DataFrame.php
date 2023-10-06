<?php

declare(strict_types=1);

/**
 * Contains the DataFrame class.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */

namespace CondorcetPHP\Oliphant;

use CondorcetPHP\Oliphant\IO\{CSV, FWF, HTML, JSON, SQL, XLSX};
use PDO;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * The DataFrame class acts as an interface to various underlying data structure, file format, and database
 * implementations.
 * @package   CondorcetPHP\Oliphant
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */
final class DataFrame extends DataFrameCore
{
    /**
     * Factory method for creating a DataFrame from a CSV file.
     * @param  $fileName
     * @param  array $options
     * @return DataFrame
     * @since  0.1.0
     */
    public static function fromCSV(string $fileName, array $options = []): self
    {
        $csv = new CSV($fileName);
        $data = $csv->loadFile($options);

        return new self($data);
    }

    /**
     * Outputs a DataFrame to a CSV file.
     * @param  $fileName
     * @param  array $options
     * @return $this
     * @throws \CondorcetPHP\Oliphant\Exceptions\FileExistsException
     * @since  0.1.0
     */
    public function toCSV(string $fileName, array $options = []): self
    {
        $csv = new CSV($fileName);
        $csv->saveFile($this->data, $options);

        return $this;
    }

    /**
     * Factory method for creating a DataFrame from a fixed-width file.
     * @param  $fileName
     * @param  array $colSpecs
     * @param  array $options
     * @return DataFrame
     * @since  0.1.0
     */
    public static function fromFWF(string $fileName, array $colSpecs, array $options = []): self
    {
        $fwf = new FWF($fileName);
        $data = $fwf->loadFile($colSpecs, $options);

        return new self($data);
    }

    /**
     * Factory method for creating a DataFrame from an XLSX worksheet.
     * @param  $fileName
     * @param  array $options
     * @return DataFrame
     * @since  0.3.0
     */
    public static function fromXLSX($fileName, array $options = []): self
    {
        $xlsx = new XLSX($fileName);
        $data = $xlsx->loadFile($options);

        return new self($data);
    }

    /**
     * Output a DataFrame as a PHPExcel worksheet.
     * @param $worksheetTitle
     * @since  0.3.0
     */
    public function toXLSXWorksheet(Spreadsheet &$excel, string $worksheetTitle): Worksheet
    {
        return XLSX::saveToWorksheet($excel, $worksheetTitle, $this->data, $this->columns);
    }

    /**
     * Factory method for instantiating a DataFrame from a SQL query.
     * @param  PDO $pdo
     * @param  $sqlQuery
     * @return DataFrame
     * @since  0.3.0
     */
    public static function fromSQL(string $sqlQuery, PDO $pdo): self
    {
        $sql = new SQL($pdo);
        $data = $sql->select($sqlQuery);

        return new self($data);
    }

    /**
     * Commits a DataFrame to a SQL database.
     * @param PDO $pdo
     * @param $tableName
     * @param array $options
     * @since 0.2.0
     */
    public function toSQL($tableName, PDO $pdo, array $options = []): void
    {
        $sql = new SQL($pdo);
        $sql->insertInto($tableName, $this->columns, $this->data, $options);
    }

    /**
     * Factory method for instantiating a DataFrame from a JSON string.
     * @param  $jsonString
     * @param  array $options
     * @return mixed
     * @since  0.4.0
     */
    public static function fromJSON($jsonString, array $options = []): self
    {
        $json = new JSON;
        $data = $json->decodeJSON($jsonString, $options);

        return new self($data);
    }

    /**
     * Converts a DataFrame to a JSON string.
     * @param  array $options
     * @return string
     * @since  0.4.0
     */
    public function toJSON(array $options = []): string
    {
        $json = new JSON;

        return $json->encodeJSON($this->data, $options);
    }

    /**
     * Outputs a DataFrame to an HTML string.
     * @param  array $options
     * @return string
     * @throws \CondorcetPHP\Oliphant\Exceptions\NotYetImplementedException
     * @since  0.1.0
     */
    public function toHTML($options = []): string
    {
        $html = new HTML($this->data);

        return $html->assembleTable($options);
    }

    /**
     * Factory method for creating a DataFrame from a two-dimensional associative array.
     * @param  array $data
     * @return DataFrame
     * @since  0.1.0
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Outputs a DataFrame as a two-dimensional associative array.
     * @return array
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
