<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

use BadMethodCallException;
use Phinx\Db\Table\Column;
use Phinx\Db\Table\ForeignKey;
use Phinx\Db\Table\Index;
use Phinx\Db\Table\Table;
use think\console\Output as OutputInterface;

/**
 * Wraps any adapter to record the time spend executing its commands
 */
class TimedOutputAdapter extends AdapterWrapper implements DirectActionInterface
{
    /**
     * @inheritDoc
     */
    public function getAdapterType(): string
    {
        return $this->getAdapter()->getAdapterType();
    }

    /**
     * Start timing a command.
     *
     * @return callable A function that is to be called when the command finishes
     */
    public function startCommandTimer(): callable
    {
        $started = microtime(true);

        return function () use ($started) {
            $end = microtime(true);
            if (OutputInterface::VERBOSITY_VERBOSE <= $this->getOutput()->getVerbosity()) {
                $this->getOutput()->writeln('    -> ' . sprintf('%.4fs', $end - $started));
            }
        };
    }

    /**
     * Write a Phinx command to the output.
     *
     * @param string $command Command Name
     * @param array $args Command Args
     * @return void
     */
    public function writeCommand(string $command, array $args = []): void
    {
        if (OutputInterface::VERBOSITY_VERBOSE > $this->getOutput()->getVerbosity()) {
            return;
        }

        if (count($args)) {
            $outArr = [];
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    $arg = array_map(
                        function ($value) {
                            return '\'' . $value . '\'';
                        },
                        $arg
                    );
                    $outArr[] = '[' . implode(', ', $arg) . ']';
                    continue;
                }

                $outArr[] = '\'' . $arg . '\'';
            }
            $this->getOutput()->writeln(' -- ' . $command . '(' . implode(', ', $outArr) . ')');

            return;
        }

        $this->getOutput()->writeln(' -- ' . $command);
    }

    /**
     * @inheritDoc
     */
    public function insert(Table $table, array $row): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('insert', [$table->getName()]);
        parent::insert($table, $row);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function bulkinsert(Table $table, array $rows): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('bulkinsert', [$table->getName()]);
        parent::bulkinsert($table, $rows);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function createTable(Table $table, array $columns = [], array $indexes = []): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('createTable', [$table->getName()]);
        parent::createTable($table, $columns, $indexes);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('changePrimaryKey', [$table->getName()]);
        $adapter->changePrimaryKey($table, $newColumns);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('changeComment', [$table->getName()]);
        $adapter->changeComment($table, $newComment);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('renameTable', [$tableName, $newTableName]);
        $adapter->renameTable($tableName, $newTableName);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('dropTable', [$tableName]);
        $adapter->dropTable($tableName);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function truncateTable(string $tableName): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('truncateTable', [$tableName]);
        parent::truncateTable($tableName);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand(
            'addColumn',
            [
                $table->getName(),
                $column->getName(),
                $column->getType(),
            ]
        );
        $adapter->addColumn($table, $column);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('renameColumn', [$tableName, $columnName, $newColumnName]);
        $adapter->renameColumn($tableName, $columnName, $newColumnName);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('changeColumn', [$tableName, $columnName, $newColumn->getType()]);
        $adapter->changeColumn($tableName, $columnName, $newColumn);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('dropColumn', [$tableName, $columnName]);
        $adapter->dropColumn($tableName, $columnName);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('addIndex', [$table->getName(), $index->getColumns()]);
        $adapter->addIndex($table, $index);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('dropIndex', [$tableName, $columns]);
        $adapter->dropIndex($tableName, $columns);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('dropIndexByName', [$tableName, $indexName]);
        $adapter->dropIndexByName($tableName, $indexName);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('addForeignKey', [$table->getName(), $foreignKey->getColumns()]);
        $adapter->addForeignKey($table, $foreignKey);
        $end();
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
            throw new BadMethodCallException('The adapter needs to implement DirectActionInterface');
        }
        $end = $this->startCommandTimer();
        $this->writeCommand('dropForeignKey', [$tableName, $columns]);
        $adapter->dropForeignKey($tableName, $columns, $constraint);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function createDatabase(string $name, array $options = []): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('createDatabase', [$name]);
        parent::createDatabase($name, $options);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function dropDatabase(string $name): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('dropDatabase', [$name]);
        parent::dropDatabase($name);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function createSchema(string $name = 'public'): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('createSchema', [$name]);
        parent::createSchema($name);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function dropSchema(string $name): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand('dropSchema', [$name]);
        parent::dropSchema($name);
        $end();
    }

    /**
     * @inheritDoc
     */
    public function executeActions(Table $table, array $actions): void
    {
        $end = $this->startCommandTimer();
        $this->writeCommand(sprintf('Altering table %s', $table->getName()));
        parent::executeActions($table, $actions);
        $end();
    }
}
