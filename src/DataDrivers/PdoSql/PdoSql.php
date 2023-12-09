<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\DataDrivers\PhpArray;

use MammothPHP\WoollyM\DataDrivers\DataDriverInterface;
use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use PDO;
use PDOStatement;

/**
 * @internal
 */
class PdoSql implements DataDriverInterface
{
    protected readonly PDOStatement $STMT_keyExist;
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
            'WHERE ' . $this->escapeColumnName() . ' = ? ' .
            'LIMIT 1 ' .
            ';'
        );

        $this->STMT_remove = $this->db->prepare(
            'DELETE FROM ' . $this->escapeTableName() . ' ' .
            'WHERE ' . $this->escapeColumnName() . ' = ? ' .
            ';'
        );
    }

    protected function escapeTableName(): string
    {
        static $r = $this->db->quote($this->table);

        return $r;
    }

    protected function escapeColumnName(): string
    {
        static $r = $this->db->quote($this->keyColumn);

        return $r;
    }

    public function setRecord(int $recordKey, array $recordData): void {}

    public function setRecordColumn(int $recordKey, int $columnKey, mixed $colValue): void {}

    public function addRecord(array $recordData): void {}

    public function removeRecord(int $recordKey): void
    {
        $this->STMT_remove->execute([$recordKey]);
    }

    public function keyExist(int $recordKey): bool
    {
        $this->STMT_keyExist->execute([$recordKey]);

        return $this->STMT_keyExist->fetch() !== false;
    }
}
