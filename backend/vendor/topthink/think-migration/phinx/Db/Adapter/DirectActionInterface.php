<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

use Phinx\Db\Table\Column;
use Phinx\Db\Table\ForeignKey;
use Phinx\Db\Table\Index;
use Phinx\Db\Table\Table;

/**
 * Represents an adapter that is capable of directly executing alter
 * instructions, without having to plan them first.
 */
interface DirectActionInterface
{
    /**
     * Renames the specified database table.
     *
     * @param string $tableName Table name
     * @param string $newName New Name
     * @return void
     */
    public function renameTable(string $tableName, string $newName): void;

    /**
     * Drops the specified database table.
     *
     * @param string $tableName Table name
     * @return void
     */
    public function dropTable(string $tableName): void;

    /**
     * Changes the primary key of the specified database table.
     *
     * @param \Phinx\Db\Table\Table $table Table
     * @param string|string[]|null $newColumns Column name(s) to belong to the primary key, or null to drop the key
     * @return void
     */
    public function changePrimaryKey(Table $table, $newColumns): void;

    /**
     * Changes the comment of the specified database table.
     *
     * @param \Phinx\Db\Table\Table $table Table
     * @param string|null $newComment New comment string, or null to drop the comment
     * @return void
     */
    public function changeComment(Table $table, ?string $newComment): void;

    /**
     * Adds the specified column to a database table.
     *
     * @param \Phinx\Db\Table\Table $table Table
     * @param \Phinx\Db\Table\Column $column Column
     * @return void
     */
    public function addColumn(Table $table, Column $column): void;

    /**
     * Renames the specified column.
     *
     * @param string $tableName Table name
     * @param string $columnName Column Name
     * @param string $newColumnName New Column Name
     * @return void
     */
    public function renameColumn(string $tableName, string $columnName, string $newColumnName): void;

    /**
     * Change a table column type.
     *
     * @param string $tableName Table name
     * @param string $columnName Column Name
     * @param \Phinx\Db\Table\Column $newColumn New Column
     * @return void
     */
    public function changeColumn(string $tableName, string $columnName, Column $newColumn): void;

    /**
     * Drops the specified column.
     *
     * @param string $tableName Table name
     * @param string $columnName Column Name
     * @return void
     */
    public function dropColumn(string $tableName, string $columnName): void;

    /**
     * Adds the specified index to a database table.
     *
     * @param \Phinx\Db\Table\Table $table Table
     * @param \Phinx\Db\Table\Index $index Index
     * @return void
     */
    public function addIndex(Table $table, Index $index): void;

    /**
     * Drops the specified index from a database table.
     *
     * @param string $tableName the name of the table
     * @param string|string[] $columns Column(s)
     * @return void
     */
    public function dropIndex(string $tableName, $columns): void;

    /**
     * Drops the index specified by name from a database table.
     *
     * @param string $tableName The table name where the index is
     * @param string $indexName The name of the index
     * @return void
     */
    public function dropIndexByName(string $tableName, string $indexName): void;

    /**
     * Adds the specified foreign key to a database table.
     *
     * @param \Phinx\Db\Table\Table $table The table to add the foreign key to
     * @param \Phinx\Db\Table\ForeignKey $foreignKey The foreign key to add
     * @return void
     */
    public function addForeignKey(Table $table, ForeignKey $foreignKey): void;

    /**
     * Drops the specified foreign key from a database table.
     *
     * @param string $tableName The table to drop the foreign key from
     * @param string[] $columns Column(s)
     * @param string|null $constraint Constraint name
     * @return void
     */
    public function dropForeignKey(string $tableName, array $columns, ?string $constraint = null): void;
}
