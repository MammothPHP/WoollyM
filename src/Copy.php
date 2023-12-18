<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use MammothPHP\WoollyM\DataDrivers\DataDriverInterface;
use MammothPHP\WoollyM\IO\SQL;
use PDO;

class Copy
{
    public ?DataDriverInterface $dataDriver = null;

    public function __construct(public readonly DataFrame $df) {}

    public function clone(): DataFrame
    {
        return clone $this->df;
    }

    public function driver(DataDriverInterface $dataDriver): self
    {
        $this->dataDriver = $dataDriver;

        return $this;
    }

    /**
     * Filter DataFrame rows using user-defined function. The parameters of the function include the row
     * being iterated over, and the index.
     */
    public function array_filter(Closure $f): DataFrame
    {
        return new ($this->df::class)(
            data: array_filter($this->df->toArray(), $f, \ARRAY_FILTER_USE_BOTH),
            dataDriver: $this->dataDriver
        );
    }

    /**
     * Returns unique values of given column(s)
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

        return new ($this->df::class)(data: $groupedData, dataDriver: $this->dataDriver);
    }

    /**
     * Allows SQL to be used to perform operations on the DataFrame
     * Table name will always be 'dataframe'
     * @throws DataFrameException
     */
    public function query(string $sql): DataFrame
    {
        $table = 'dataframe';
        $sql = trim($sql);
        $queryType = trim(strtoupper(strtok($sql, ' ')));

        $pdo = new PDO('sqlite::memory:');

        $sqlColumns = implode(', ', $this->df->columnsNames());

        $pdo->exec("DROP TABLE IF EXISTS {$table};");
        $pdo->exec("CREATE TABLE IF NOT EXISTS dataframe ({$sqlColumns});");

        SQL::fromDataFrame($this->df)->toPDO($pdo, $table);

        if ($queryType === 'SELECT') {
            $result = $pdo->query($sql);
        } else {
            $pdo->exec($sql);
            $result = $pdo->query("SELECT * FROM {$table};");
        }

        $results = $result->fetchAll(PDO::FETCH_ASSOC);

        $pdo->exec("DROP TABLE IF EXISTS {$table};");

        return new ($this->df::class)(data: $results, dataDriver: $this->dataDriver);
    }
}
