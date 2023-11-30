<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\IO\Wrappers;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\SQL;
use PDO;

trait SqlWrapper
{
    /**
     * Factory method for instantiating a DataFrame from a SQL query.
     */
    public static function fromSQL(string $sqlQuery, PDO $pdo): self
    {
        $sql = new SQL($pdo);
        $data = $sql->select($sqlQuery);

        return new self($data);
    }

    /**
     * Commits a DataFrame to a SQL database.
     */
    public function toSQL(string $tableName, PDO $pdo, array $options = []): void
    {
        $sql = new SQL($pdo);
        $sql->insertInto($tableName, $this->columnsNames(), $this->toArray(), $options);
    }
}
