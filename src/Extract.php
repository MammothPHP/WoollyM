<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use MammothPHP\WoollyM\IO\SQL;
use MammothPHP\WoollyM\Stats\AggProvider;
use PDO;

class Extract
{
    use LinkedDataFrame;

    public function __construct(DataFrame $df, public readonly DataFrame $to)
    {
        $this->setLinkedDataFrame($df);
    }

    public function clone(): DataFrame
    {
        return clone $this->getLinkedDataFrame();
    }

    /**
     * Filter DataFrame rows using user-defined function. The parameters of the function include the row
     * being iterated over, and the index.
     */
    public function withFilter(Closure $f): DataFrame
    {
        return $this->to->insert()->append(array_filter($this->getLinkedDataFrame()->toArray(), $f, \ARRAY_FILTER_USE_BOTH));
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

        foreach ($this->getLinkedDataFrame() as $row) {
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

        return $this->to->insert()->append($groupedData);
    }

    /**
     * Allows SQL to be used to perform operations on the DataFrame
     * Table name will always be 'dataframe'
     */
    public function fromSqlQuery(string $sql): DataFrame
    {
        $table = 'dataframe';
        $sql = trim($sql);
        $queryType = trim(strtoupper(strtok($sql, ' ')));

        $pdo = new PDO('sqlite::memory:');

        $sqlColumns = implode(', ', $this->getLinkedDataFrame()->columnsNames());

        $pdo->exec("DROP TABLE IF EXISTS {$table};");
        $pdo->exec("CREATE TABLE IF NOT EXISTS dataframe ({$sqlColumns});");

        SQL::fromDataFrame($this->getLinkedDataFrame())->toPDO($pdo, $table);

        if ($queryType === 'SELECT') {
            $result = $pdo->query($sql);
        } else {
            $pdo->exec($sql);
            $result = $pdo->query("SELECT * FROM {$table};");
        }

        $results = $result->fetchAll(PDO::FETCH_ASSOC);

        $pdo->exec("DROP TABLE IF EXISTS {$table};");

        return $this->to->insert()->append($results);
    }

    public function groupBy(string|AggProvider ...$args): DataFrame
    {
        $stmt = $this->df->select();

        foreach ($args as $arg) {
            $stmt->select($arg);

            if (\is_string($arg)) {
                $stmt->groupBy($arg);
            }
        }

        return $stmt->export();
    }
}
