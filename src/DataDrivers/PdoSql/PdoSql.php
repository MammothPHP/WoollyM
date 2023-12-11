<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers\PdoSql;

use ArrayIterator;
use Exception;
use MammothPHP\WoollyM\DataDrivers\DataDriverInterface;
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\Exceptions\{FeatureNotImplementedYet, NotYetImplementedException};
use PDO;
use PDOStatement;
use Traversable;

/**
 * @internal
 */
class PdoSql implements DataDriverInterface
{
    public const string COLUMN_KEY_TYPE = 'name';

    protected readonly PDOStatement $STMT_keyExist;
    protected readonly PDOStatement $STMT_getRecordKey;
    protected readonly PDOStatement $STMT_remove;

    public function __construct(
        public readonly PDO $db,
        public readonly string $table = 'DataFrame',
        public readonly string $keyColumn = 'id',
        public bool $isWritable = true,
        public readonly bool $createMode = false,
    ) {
        // Cannot create table an column if it's not writable
        if (!$this->isWritable && $this->createMode) {
            throw new NotYetImplementedException;
        }

        // Force PDO exception
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare statements
        $this->STMT_keyExist = $this->db->prepare(
            'SELECT 1 ' .
            'FROM ' . $this->escapeTableName() . ' ' .
            'WHERE ' . $this->escapePrimaryKeyColumnName() . ' = ? ' .
            'LIMIT 1 ' .
            ';'
        );

        $this->STMT_getRecordKey = $this->db->prepare(
            'SELECT * ' .
            'FROM ' . $this->escapeTableName() . ' ' .
            'WHERE ' . $this->escapePrimaryKeyColumnName() . ' = ? ' .
            'LIMIT 1 ' .
            ';'
        );

        $this->STMT_remove = $this->db->prepare(
            'DELETE FROM ' . $this->escapeTableName() . ' ' .
            'WHERE ' . $this->escapePrimaryKeyColumnName() . ' = ? ' .
            ';'
        );
    }

    public function count(): int
    {
        $st = $this->db->query('SELECT count(' . $this->escapePrimaryKeyColumnName() . ') FROM ' . $this->escapeTableName() . ';');

        return (int) $st->fetch(mode: PDO::FETCH_NUM)[0];
    }

    protected function escapeTableName(): string
    {
        static $r = $this->db->quote($this->table);

        return $r;
    }

    protected function escapePrimaryKeyColumnName(): string
    {
        static $r = $this->escapeColumnName($this->keyColumn);

        return $r;
    }

    protected function escapeColumnName(string $columnName): string
    {
        return trim(str_replace("'", '', $this->db->quote($columnName)));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator;
    }

    public function getRecordKey(int $recordKey): array
    {
        $this->STMT_getRecordKey->bindValue(1, $recordKey, PDO::PARAM_INT);
        $this->STMT_getRecordKey->execute();

        $r = $this->STMT_getRecordKey->fetch(PDO::FETCH_ASSOC);

        if ($r === false) {
            throw new KeyNotExistException;
        }

        return $r;
    }

    public function setRecord(int $recordKey, array $recordData): void
    {
        $recordData[$this->keyColumn] = $recordKey;

        $this->addRecord($recordData);
    }

    public function addRecord(array $recordData): void
    {
        $columns = array_keys($recordData);
        array_walk($columns, fn(string $c): string => $this->escapeColumnName($c));
        $columns = implode(',', $columns);

        $query = 'INSERT OR REPLACE INTO ' . $this->escapeTableName() . ' (' . $columns . ') VALUES (?' . str_repeat(',?', \count($recordData) - 1) . ')';
        var_dump($query);
        $this->db->prepare($query)->execute(array_values($recordData));
    }

    public function setRecordColumn(int $recordKey, int|string $columnKey, mixed $colValue): void
    {
        // Not supported
        throw new FeatureNotImplementedYet;
    }

    public function removeRecord(int $recordKey): void
    {
        $this->STMT_remove->bindValue(1, $recordKey, PDO::PARAM_INT);
        $this->STMT_remove->execute();
    }

    public function keyExist(int $recordKey): bool
    {
        $this->STMT_keyExist->bindValue(1, $recordKey, PDO::PARAM_INT);
        $this->STMT_keyExist->execute();

        return $this->STMT_keyExist->fetch(PDO::FETCH_NUM) !== false;
    }
}
