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

    public function __construct(public readonly DataFrame $df, public readonly DataFrame $to) {}

    public function clone(): DataFrame
    {
        return clone $this->df;
    }

    /**
     * Filter DataFrame rows using user-defined function. The parameters of the function include the row
     * being iterated over, and the index.
     */
    public function filter(Closure $f): DataFrame
    {
        return $this->to->append(array_filter($this->df->toArray(), $f, \ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Returns unique values of given column(s)
     */
    public function unique(array|string $onColumns): DataFrame
    {
        if (!\is_array($onColumns)) {
            $onColumns = [$onColumns];
        }

        $groupedData = [];
        $uniqueColumns = [];
        foreach ($this->df as $row) {
            $uniqueKey = null;
            foreach ($onColumns as $column) {
                $uniqueKey .= $row[$column];
            }

            if (isset($uniqueColumns[$uniqueKey])) {
                continue;
            } else {
                $uniqueColumns[$uniqueKey] = true;

                $new_row = [];
                foreach ($onColumns as $column) {
                    $new_row[$column] = $row[$column];
                }

                $groupedData[] = $new_row;
            }
        }

        return $this->to->append($groupedData);
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

        return $this->to->append($results);
    }
}
