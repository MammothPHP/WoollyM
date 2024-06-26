<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use MammothPHP\WoollyM\DataDrivers\DataDriverInterface;
use MammothPHP\WoollyM\IO\SQL;
use MammothPHP\WoollyM\Stats\{AggInterface, AggProvider};
use PDO;

class Extract
{
    use LinkedDataFrame;

    public ?DataDriverInterface $dataDriver = null;

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
     * @throws DataFrameException
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

    public function group(string|AggProvider ...$args): DataFrame
    {
        // Check invalid columns
        array_walk($args, fn(string|AggProvider $col) => $this->df->mustHaveColumn(\is_string($col) ? $col : $col->column));


        $r = [];

        foreach ($this->df as $record) {
            $hash = hash_init('sha224');

            foreach ($args as $col) {
                if (\is_string($col)) {
                    hash_update($hash, serialize($record[$col] ?? null));
                }
            }

            $hash = hash_final($hash, false);

            if (!isset($r[$hash])) {
                foreach ($args as $col) {
                    if (\is_string($col)) {
                        $r[$hash][$col] = $record[$col] ?? null;
                    } else {
                        $r[$hash][$col->as] = $col->getAggObjectProvider();
                    }
                }
            }

            foreach ($args as $col) {
                if (!\is_string($col)) {
                    $r[$hash][$col->as]->addValue($record[$col->column]);
                }
            }
        }

        foreach ($r as &$record) {
            foreach ($record as &$value) {
                if ($value instanceof AggInterface) {
                    $value = $value->getResult();
                }
            }
        }

        return DataFrame::fromArray($r);
    }
}
