<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use PDO;

class Copy
{
    public function __construct(public readonly DataFrame $df) {}

    public function clone(): DataFrame
    {
        return clone $this->df;
    }

    /**
     * Filter DataFrame rows using user-defined function. The parameters of the function include the row
     * being iterated over, and the index.
     */
    public function array_filter(Closure $f): DataFrame
    {
        return DataFrame::fromArray(array_filter($this->df->toArray(), $f, \ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Returns unique values of given column(s)
     *
     */
    public function unique(array|string $columns): DataFrame
    {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }

        $groupedData = [];
        $uniqueColumns = [];
        foreach ($this->df as $row) {
            $uniqueKey = null;
            foreach ($columns as $column) {
                $uniqueKey .= $row[$column];
            }

            if (isset($uniqueColumns[$uniqueKey])) {
                continue;
            } else {
                $uniqueColumns[$uniqueKey] = true;

                $new_row = [];
                foreach ($columns as $column) {
                    $new_row[$column] = $row[$column];
                }

                $groupedData[] = $new_row;
            }
        }

        return DataFrame::fromArray($groupedData);
    }

    /**
     * Allows SQL to be used to perform operations on the DataFrame
     * Table name will always be 'dataframe'
     * @throws DataFrameException
     */
    public function query(string $sql): DataFrame
    {
        $sql = trim($sql);
        $queryType = trim(strtoupper(strtok($sql, ' ')));

        $pdo = new PDO('sqlite::memory:');

        $sqlColumns = implode(', ', $this->df->columnsNames());

        $pdo->exec('DROP TABLE IF EXISTS dataframe;');
        $pdo->exec("CREATE TABLE IF NOT EXISTS dataframe ({$sqlColumns});");

        $this->df->toSQL('dataframe', $pdo);

        if ($queryType === 'SELECT') {
            $result = $pdo->query($sql);
        } else {
            $pdo->exec($sql);
            $result = $pdo->query('SELECT * FROM dataframe;');
        }

        $results = $result->fetchAll(PDO::FETCH_ASSOC);

        $pdo->exec('DROP TABLE IF EXISTS dataframe;');

        return DataFrame::fromArray($results);
    }
}
