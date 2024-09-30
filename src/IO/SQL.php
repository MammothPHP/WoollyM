<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\InvalidSelectException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class SQL
{
    use BuilderExport;

    public int $chunkSize = 500;
    public bool $replace = false;
    public bool $ignore = false;

    public readonly PDO $pdo;
    public readonly string $query;

    protected PDOStatement $preparedStatement;
    protected string $statementCacheKey = '';

    protected static function configurePdo(PDO $pdo): PDO
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function fromSql(PDO $pdo, string $query): static
    {
        self::configurePdo($pdo);

        $builder = new static;
        $builder->pdo = self::configurePdo($pdo);
        $builder->query = $query;

        return $builder;
    }

    final public function __construct() {}

    public function import(DataFrame $df = new DataFrame): DataFrame
    {
        $stmt = $this->pdo->query($this->query);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $df->addRecord($row);
        }

        return $df;
    }

    public function toPDO(PDO $pdo, string $tableName): void
    {
        $this->pdo = self::configurePdo($pdo);
        $this->insertInto($tableName);
    }

    /**
     * @param $chunkSize - Insert chunk size
     * @param $replace - Use a sql replace statement
     * @param $ignore - Use a sql ignore statement
     */
    public function options(int $chunkSize = 500, bool $replace = false, bool $ignore = false): static
    {
        $this->chunkSize = $chunkSize;
        $this->replace = $replace;
        $this->ignore = $ignore;

        return $this;
    }

    /**
     * Performs a SQL insert transaction to provided table, crafting the SQL statement using an array of columns
     * and a two-dimensional array of data.
     * @throws InvalidSelectException
     */
    protected function insertInto(string $tableName): int
    {
        if (\count($this->fromDf) === 0) {
            return 0;
        }

        $columns = $this->fromDf->columnsNames();

        try {
            $this->identifyAnyMissingColumns($columns, $tableName);
        } catch (PDOException) { // @phpstan-ignore catch.neverThrown
            // If this function throws a PDO exception then it's probably just a unit test running a SQLite query
            // SQLite doesn't support "show columns like" syntax
        } catch (InvalidSelectException $ice) {
            throw $ice;
        }

        $this->pdo->beginTransaction();
        $affected = 0;
        $chunk = [];

        try {
            foreach ($this->fromDf->getRecordsAsArrayIterator(true) as $record) {
                $chunk[] = $record;

                if (\count($chunk) >= $this->chunkSize) {
                    $affected += $this->insertChunkedData($tableName, $columns, $chunk);
                    $chunk = [];
                }
            }

            if (!empty($chunk)) {
                $affected += $this->insertChunkedData($tableName, $columns, $chunk);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
        } finally {
            ($e ?? null) instanceof PDOException && throw $e;
        }

        $this->pdo->commit();

        return $affected;
    }


    /**
     * Transforms and executes a series of prepared statements from a chunked array.
     */
    protected function insertChunkedData(string $tableName, array $columns, array $data): int
    {
        $affected = 0;

        $this->createPreparedStatement($tableName, $columns, \count($data));

        $arg = [];
        foreach ($data as $record) {
            foreach ($record as $element) {
                $arg[] = $element;
            }
        }

        $this->preparedStatement->execute($arg);
        $affected += $this->preparedStatement->rowCount();

        return $affected;
    }

    /**
     * Transforms a table string, array of columns, and array of data into a prepared statement.
     */
    protected function createPreparedStatement(string $tableName, array $columns, int $rows): void
    {
        $cacheKey = "{$rows}/{$this->replace}/{$this->ignore}";

        if ($this->statementCacheKey === $cacheKey) {
            return;
        }

        if ($this->replace && $this->ignore) {
            throw new RuntimeException('REPLACE and INSERT IGNORE are mutually exclusive. Please choose only one.');
        }

        $countColumns = \count($columns);
        $columns = '(' . implode(', ', $columns) . ')';

        $data = '';

        for ($ri = 0; $ri < $rows; $ri++) {
            $data .= '(?';
            $data .= str_repeat(',?', $countColumns - 1);
            $data .= ')';

            if (($ri + 1) < $rows) {
                $data .= ',';
            }
        }

        if ($this->replace) {
            $insert = 'REPLACE';
        } elseif ($this->ignore === true) {
            $insert = 'INSERT IGNORE';
        } else {
            $insert = 'INSERT';
        }

        $sql = "{$insert} INTO {$tableName} {$columns} VALUES {$data};";

        $this->preparedStatement = $this->pdo->prepare($sql);
        $this->statementCacheKey = $cacheKey;
    }

    /**
     * Identifies any missing columns in the database which we may be attempting to insert.
     *
     * @throws InvalidSelectException
     */
    protected function identifyAnyMissingColumns(array $columns, string $tableName): void
    {
        $db_columns = array_column($this->pdo->query("SHOW COLUMNS FROM {$tableName};")->fetchAll(), 'Field');

        $missingColumns = array_diff($columns, $db_columns);

        if (\count($missingColumns) !== 0) {
            $s = \count($missingColumns) > 1 ? 's' : '';
            $missingColumns = '`' . implode('`, `', $missingColumns) . '`';

            throw new InvalidSelectException("Error: Table {$tableName} does not contain the column{$s}: {$missingColumns}.");
        }
    }
}
