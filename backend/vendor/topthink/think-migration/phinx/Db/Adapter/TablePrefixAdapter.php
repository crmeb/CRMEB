<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

use BadMethodCallException;
use InvalidArgumentException;
use Phinx\Db\Action\AddColumn;
use Phinx\Db\Action\AddForeignKey;
use Phinx\Db\Action\AddIndex;
use Phinx\Db\Action\ChangeColumn;
use Phinx\Db\Action\ChangeComment;
use Phinx\Db\Action\ChangePrimaryKey;
use Phinx\Db\Action\DropForeignKey;
use Phinx\Db\Action\DropIndex;
use Phinx\Db\Action\DropTable;
use Phinx\Db\Action\RemoveColumn;
use Phinx\Db\Action\RenameColumn;
use Phinx\Db\Action\RenameTable;
use Phinx\Db\Table\Column;
use Phinx\Db\Table\ForeignKey;
use Phinx\Db\Table\Index;
use Phinx\Db\Table\Table;

/**
 * Table prefix/suffix adapter.
 *
 * Used for inserting a prefix or suffix into table names.
 *
 * @author Samuel Fisher <sam@sfisher.co>
 */
class TablePrefixAdapter extends AdapterWrapper implements DirectActionInterface
{
    /**
     * @inheritDoc
     */
    public function getAdapterType(): string
    {
        return 'TablePrefixAdapter';
    }

    /**
     * @inheritDoc
     */
    public function hasTable(string $tableName): bool
    {
        $adapterTableName = $this->getAdapterTableName($tableName);

        return parent::hasTable($adapterTableName);
    }

    /**
     * @inheritDoc
     */
    public function createTable(Table $table, array $columns = [], array $indexes = []): void
    {
        $adapterTable = new Table(
            $this->getAdapterTableName($table->getName()),
            $table->getOptions()
        );
        parent::createTable($adapterTable, $columns, $indexes);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function changePrimaryKey(Table $table, $newColumns): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }

        $adapterTable = new Table(
            $this->getAdapterTableName($table->getName()),
            $table->getOptions()
        );
        $adapter->changePrimaryKey($adapterTable, $newColumns);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function changeComment(Table $table, ?string $newComment): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }

        $adapterTable = new Table(
            $this->getAdapterTableName($table->getName()),
            $table->getOptions()
        );
        $adapter->changeComment($adapterTable, $newComment);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function renameTable(string $tableName, string $newTableName): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }

        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapterNewTableName = $this->getAdapterTableName($newTableName);
        $adapter->renameTable($adapterTableName, $adapterNewTableName);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function dropTable(string $tableName): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropTable($adapterTableName);
    }

    /**
     * @inheritDoc
     */
    public function truncateTable(string $tableName): void
    {
        $adapterTableName = $this->getAdapterTableName($tableName);
        parent::truncateTable($adapterTableName);
    }

    /**
     * @inheritDoc
     */
    public function getColumns(string $tableName): array
    {
        $adapterTableName = $this->getAdapterTableName($tableName);

        return parent::getColumns($adapterTableName);
    }

    /**
     * @inheritDoc
     */
    public function hasColumn(string $tableName, string $columnName): bool
    {
        $adapterTableName = $this->getAdapterTableName($tableName);

        return parent::hasColumn($adapterTableName, $columnName);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function addColumn(Table $table, Column $column): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        $adapter->addColumn($adapterTable, $column);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function renameColumn(string $tableName, string $columnName, string $newColumnName): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->renameColumn($adapterTableName, $columnName, $newColumnName);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function changeColumn(string $tableName, string $columnName, Column $newColumn): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->changeColumn($adapterTableName, $columnName, $newColumn);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function dropColumn(string $tableName, string $columnName): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropColumn($adapterTableName, $columnName);
    }

    /**
     * @inheritDoc
     */
    public function hasIndex(string $tableName, $columns): bool
    {
        $adapterTableName = $this->getAdapterTableName($tableName);

        return parent::hasIndex($adapterTableName, $columns);
    }

    /**
     * @inheritDoc
     */
    public function hasIndexByName(string $tableName, string $indexName): bool
    {
        $adapterTableName = $this->getAdapterTableName($tableName);

        return parent::hasIndexByName($adapterTableName, $indexName);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function addIndex(Table $table, Index $index): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTable = new Table($table->getName(), $table->getOptions());
        $adapter->addIndex($adapterTable, $index);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function dropIndex(string $tableName, $columns): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropIndex($adapterTableName, $columns);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function dropIndexByName(string $tableName, string $indexName): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropIndexByName($adapterTableName, $indexName);
    }

    /**
     * @inheritDoc
     */
    public function hasPrimaryKey(string $tableName, $columns, ?string $constraint = null): bool
    {
        $adapterTableName = $this->getAdapterTableName($tableName);

        return parent::hasPrimaryKey($adapterTableName, $columns, $constraint);
    }

    /**
     * @inheritDoc
     */
    public function hasForeignKey(string $tableName, $columns, ?string $constraint = null): bool
    {
        $adapterTableName = $this->getAdapterTableName($tableName);

        return parent::hasForeignKey($adapterTableName, $columns, $constraint);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function addForeignKey(Table $table, ForeignKey $foreignKey): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        $adapter->addForeignKey($adapterTable, $foreignKey);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function dropForeignKey(string $tableName, array $columns, ?string $constraint = null): void
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DirectActionInterface) {
            throw new BadMethodCallException('The underlying adapter does not implement DirectActionInterface');
        }
        $adapterTableName = $this->getAdapterTableName($tableName);
        $adapter->dropForeignKey($adapterTableName, $columns, $constraint);
    }

    /**
     * @inheritDoc
     */
    public function insert(Table $table, array $row): void
    {
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        parent::insert($adapterTable, $row);
    }

    /**
     * @inheritDoc
     */
    public function bulkinsert(Table $table, array $rows): void
    {
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());
        parent::bulkinsert($adapterTable, $rows);
    }

    /**
     * Gets the table prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return (string)$this->getOption('table_prefix');
    }

    /**
     * Gets the table suffix.
     *
     * @return string
     */
    public function getSuffix(): string
    {
        return (string)$this->getOption('table_suffix');
    }

    /**
     * Applies the prefix and suffix to the table name.
     *
     * @param string $tableName Table name
     * @return string
     */
    public function getAdapterTableName(string $tableName): string
    {
        return $this->getPrefix() . $tableName . $this->getSuffix();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    public function executeActions(Table $table, array $actions): void
    {
        $adapterTableName = $this->getAdapterTableName($table->getName());
        $adapterTable = new Table($adapterTableName, $table->getOptions());

        foreach ($actions as $k => $action) {
            switch (true) {
                case $action instanceof AddColumn:
                    /** @var \Phinx\Db\Action\AddColumn $action */
                    $actions[$k] = new AddColumn($adapterTable, $action->getColumn());
                    break;

                case $action instanceof AddIndex:
                    /** @var \Phinx\Db\Action\AddIndex $action */
                    $actions[$k] = new AddIndex($adapterTable, $action->getIndex());
                    break;

                case $action instanceof AddForeignKey:
                    /** @var \Phinx\Db\Action\AddForeignKey $action */
                    $foreignKey = clone $action->getForeignKey();
                    $refTable = $foreignKey->getReferencedTable();
                    $refTableName = $this->getAdapterTableName($refTable->getName());
                    $foreignKey->setReferencedTable(new Table($refTableName, $refTable->getOptions()));
                    $actions[$k] = new AddForeignKey($adapterTable, $foreignKey);
                    break;

                case $action instanceof ChangeColumn:
                    /** @var \Phinx\Db\Action\ChangeColumn $action */
                    $actions[$k] = new ChangeColumn($adapterTable, $action->getColumnName(), $action->getColumn());
                    break;

                case $action instanceof DropForeignKey:
                    /** @var \Phinx\Db\Action\DropForeignKey $action */
                    $actions[$k] = new DropForeignKey($adapterTable, $action->getForeignKey());
                    break;

                case $action instanceof DropIndex:
                    /** @var \Phinx\Db\Action\DropIndex $action */
                    $actions[$k] = new DropIndex($adapterTable, $action->getIndex());
                    break;

                case $action instanceof DropTable:
                    /** @var \Phinx\Db\Action\DropTable $action */
                    $actions[$k] = new DropTable($adapterTable);
                    break;

                case $action instanceof RemoveColumn:
                    /** @var \Phinx\Db\Action\RemoveColumn $action */
                    $actions[$k] = new RemoveColumn($adapterTable, $action->getColumn());
                    break;

                case $action instanceof RenameColumn:
                    /** @var \Phinx\Db\Action\RenameColumn $action */
                    $actions[$k] = new RenameColumn($adapterTable, $action->getColumn(), $action->getNewName());
                    break;

                case $action instanceof RenameTable:
                    /** @var \Phinx\Db\Action\RenameTable $action */
                    $actions[$k] = new RenameTable($adapterTable, $this->getAdapterTableName($action->getNewName()));
                    break;

                case $action instanceof ChangePrimaryKey:
                    /** @var \Phinx\Db\Action\ChangePrimaryKey $action */
                    $actions[$k] = new ChangePrimaryKey($adapterTable, $action->getNewColumns());
                    break;

                case $action instanceof ChangeComment:
                    /** @var \Phinx\Db\Action\ChangeComment $action */
                    $actions[$k] = new ChangeComment($adapterTable, $action->getNewComment());
                    break;

                default:
                    throw new InvalidArgumentException(
                        sprintf("Forgot to implement table prefixing for action: '%s'", get_class($action))
                    );
            }
        }

        parent::executeActions($adapterTable, $actions);
    }
}
