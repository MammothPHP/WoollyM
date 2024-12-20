<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers\PdoSql;

use Iterator;
use MammothPHP\WoollyM\DataDrivers\{ColumnKeyType, WritableDriver};
use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\Exceptions\{FeatureNotImplementedYet, NotYetImplementedException};
use PDO;
use PDOStatement;

/**
 * @internal
 */
class PdoSql implements WritableDriver
{
    public const ColumnKeyType COLUMN_KEY_TYPE = ColumnKeyType::COLUMN_NAME;

    protected readonly PDOStatement $STMT_keyExist;
    protected readonly PDOStatement $STMT_getRecordKey;
    protected readonly PDOStatement $STMT_delete;

    public function __construct(
        public readonly PDO $db,
        public readonly string $table = 'DataFrame',
        public readonly string $keyColumn = 'id',
        public bool $isWritable = true,
        public readonly bool $createMode = false,
    ) {
        // Cannot create table and column if it's not writable
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

        $this->STMT_delete = $this->db->prepare(
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

    public function getIterator(): Iterator
    {
        $stmt = $this->db->query('SELECT * FROM ' . $this->escapeTableName() . ';');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $iterator = $stmt->getIterator();

        return new class ($iterator, $this->keyColumn) implements Iterator { // @phpstan-ignore argument.type (phpstan bug)
            public function __construct(public readonly Iterator $iterator, public readonly string $keyColumn) {}

            public function current(): mixed
            {
                return $this->iterator->current();
            }

            public function key(): mixed
            {
                return $this->iterator->current()[$this->keyColumn];
            }

            public function next(): void
            {
                $this->iterator->next();
            }

            public function rewind(): void
            {
                $this->iterator->rewind();
            }

            public function valid(): bool
            {
                return $this->iterator->valid();
            }
        };
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
        $this->db->prepare($query)->execute(array_values($recordData));
    }

    public function setRecordColumn(int $recordKey, int|string $columnKey, mixed $colValue): void
    {
        // Not supported
        throw new FeatureNotImplementedYet;
    }

    public function deleteRecord(int $recordKey): void
    {
        $this->STMT_delete->bindValue(1, $recordKey, PDO::PARAM_INT);
        $this->STMT_delete->execute();
    }

    public function keyExist(int $recordKey): bool
    {
        $this->STMT_keyExist->bindValue(1, $recordKey, PDO::PARAM_INT);
        $this->STMT_keyExist->execute();

        return $this->STMT_keyExist->fetch(PDO::FETCH_NUM) !== false;
    }
}
