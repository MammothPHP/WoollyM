<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO;

use MammothPHP\WoollyM\Exceptions\InvalidSelectException;
use PDO;
use PDOException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class SQL
{
    private $defaultOptions = [
        'chunksize' => 5000,
        'replace' => false,
        'ignore' => false,
    ];

    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
    }

    /**
     * Performs a SQL select, returning an associative array of the results.
     */
    public function select($sqlQuery): array
    {
        $pdo = $this->pdo;
        $query = $pdo->query($sqlQuery);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Performs a SQL insert transaction to provided table, crafting the SQL statement using an array of columns
     * and a two-dimensional array of data.
     * @throws InvalidSelectException
     */
    public function insertInto($tableName, array $columns, array $data, $options = []): int
    {
        if (\count($data) === 0) {
            return 0;
        }

        try {
            $this->identifyAnyMissingColumns($columns, $tableName);
        } catch (PDOException $pdoe) {
            // If this function throws a PDO exception then it's probably just a unit test running a SQLite query
            // SQLite doesn't support "show columns like" syntax
        } catch (InvalidSelectException $ice) {
            throw $ice;
        }

        $pdo = $this->pdo;

        $options = Options::setDefaultOptions($options, $this->defaultOptions);
        $chunksizeOpt = $options['chunksize'];

        $pdo->beginTransaction();

        try {
            $data = array_chunk($data, $chunksizeOpt);
            $affected = $this->insertChunkedData($pdo, $tableName, $columns, $data, $options);
        } catch (PDOException $e) {
            $pdo->rollBack();

            throw $e;
        }
        $pdo->commit();

        return $affected;
    }


    /**
     * Transforms and executes a series of prepared statements from a chunked array.
     * @internal
     */
    private function insertChunkedData(PDO $pdo, $tableName, array $columns, array $data, array $options): int
    {
        $affected = 0;
        foreach ($data as $chunk) {
            $sql = $this->createPreparedStatement($tableName, $columns, $chunk, $options);
            $stmt = $pdo->prepare($sql);
            $chunk = $this->flattenArray($chunk);
            $stmt->execute($chunk);
            $affected += $stmt->rowCount();
        }

        return $affected;
    }

    /**
     * Transforms a table string, array of columns, and array of data into a prepared statement.
     * @internal
     */
    private function createPreparedStatement($tableName, array $columns, array $data, array $options): string
    {
        $replaceOpt = $options['replace'];
        $ignoreOpt = $options['ignore'];

        if ($replaceOpt === true && $ignoreOpt === true) {
            throw new RuntimeException('REPLACE and INSERT IGNORE are mutually exclusive. Please choose only one.');
        }

        $columns = '(' . implode(', ', $columns) . ')';

        foreach ($data as &$row) {
            $row = array_fill(0, \count($row), '?');
            $row = '(' . implode(', ', $row) . ')';
        }
        $data = implode(', ', $data);

        if ($replaceOpt === true) {
            $insert = 'REPLACE';
        } elseif ($ignoreOpt === true) {
            $insert = 'INSERT IGNORE';
        } else {
            $insert = 'INSERT';
        }

        return "{$insert} INTO {$tableName} {$columns} VALUES {$data};";
    }

    /**
     * Flattens a two-dimensional array into a one-dimensional array.
     * @internal
     */
    private function flattenArray(array $array): array
    {
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

        $result = [];
        foreach ($it as $element) {
            $result[] = $element;
        }

        return $result;
    }

    /**
     * Identifies any missing columns in the database which we may be attempting to insert.
     *
     * @throws InvalidSelectException
     */
    private function identifyAnyMissingColumns(array $columns, $tableName): void
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
