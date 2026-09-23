<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

use Cake\Database\Query;
use Phinx\Db\Table\Column;
use Phinx\Db\Table\Table;
use Phinx\Migration\MigrationInterface;
use think\console\Input as InputInterface;
use think\console\Output as OutputInterface;

/**
 * Adapter Wrapper.
 *
 * Proxy commands through to another adapter, allowing modification of
 * parameters during calls.
 *
 * @author Woody Gilk <woody.gilk@gmail.com>
 */
abstract class AdapterWrapper implements AdapterInterface, WrapperInterface
{
    /**
     * @var \Phinx\Db\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @inheritDoc
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * @inheritDoc
     */
    public function setAdapter(AdapterInterface $adapter): AdapterInterface
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): AdapterInterface
    {
        $this->adapter->setOptions($options);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return $this->adapter->getOptions();
    }

    /**
     * @inheritDoc
     */
    public function hasOption(string $name): bool
    {
        return $this->adapter->hasOption($name);
    }

    /**
     * @inheritDoc
     */
    public function getOption(string $name)
    {
        return $this->adapter->getOption($name);
    }

    /**
     * @inheritDoc
     */
    public function setInput(InputInterface $input): AdapterInterface
    {
        $this->adapter->setInput($input);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInput(): InputInterface
    {
        return $this->adapter->getInput();
    }

    /**
     * @inheritDoc
     */
    public function setOutput(OutputInterface $output): AdapterInterface
    {
        $this->adapter->setOutput($output);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOutput(): OutputInterface
    {
        return $this->adapter->getOutput();
    }

    /**
     * @inheritDoc
     */
    public function getColumnForType(string $columnName, string $type, array $options): Column
    {
        return $this->adapter->getColumnForType($columnName, $type, $options);
    }

    /**
     * @inheritDoc
     */
    public function connect(): void
    {
        $this->getAdapter()->connect();
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): void
    {
        $this->getAdapter()->disconnect();
    }

    /**
     * @inheritDoc
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->getAdapter()->execute($sql, $params);
    }

    /**
     * @inheritDoc
     */
    public function query(string $sql, array $params = [])
    {
        return $this->getAdapter()->query($sql, $params);
    }

    /**
     * @inheritDoc
     */
    public function insert(Table $table, array $row): void
    {
        $this->getAdapter()->insert($table, $row);
    }

    /**
     * @inheritDoc
     */
    public function bulkinsert(Table $table, array $rows): void
    {
        $this->getAdapter()->bulkinsert($table, $rows);
    }

    /**
     * @inheritDoc
     */
    public function fetchRow(string $sql)
    {
        return $this->getAdapter()->fetchRow($sql);
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(string $sql): array
    {
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * @inheritDoc
     */
    public function getVersions(): array
    {
        return $this->getAdapter()->getVersions();
    }

    /**
     * @inheritDoc
     */
    public function getVersionLog(): array
    {
        return $this->getAdapter()->getVersionLog();
    }

    /**
     * @inheritDoc
     */
    public function migrated(MigrationInterface $migration, string $direction, string $startTime, string $endTime): AdapterInterface
    {
        $this->getAdapter()->migrated($migration, $direction, $startTime, $endTime);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toggleBreakpoint(MigrationInterface $migration): AdapterInterface
    {
        $this->getAdapter()->toggleBreakpoint($migration);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resetAllBreakpoints(): int
    {
        return $this->getAdapter()->resetAllBreakpoints();
    }

    /**
     * @inheritDoc
     */
    public function setBreakpoint(MigrationInterface $migration): AdapterInterface
    {
        $this->getAdapter()->setBreakpoint($migration);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unsetBreakpoint(MigrationInterface $migration): AdapterInterface
    {
        $this->getAdapter()->unsetBreakpoint($migration);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createSchemaTable(): void
    {
        $this->getAdapter()->createSchemaTable();
    }

    /**
     * @inheritDoc
     */
    public function getColumnTypes(): array
    {
        return $this->getAdapter()->getColumnTypes();
    }

    /**
     * @inheritDoc
     */
    public function isValidColumnType(Column $column): bool
    {
        return $this->getAdapter()->isValidColumnType($column);
    }

    /**
     * @inheritDoc
     */
    public function hasTransactions(): bool
    {
        return $this->getAdapter()->hasTransactions();
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        $this->getAdapter()->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function commitTransaction(): void
    {
        $this->getAdapter()->commitTransaction();
    }

    /**
     * @inheritDoc
     */
    public function rollbackTransaction(): void
    {
        $this->getAdapter()->rollbackTransaction();
    }

    /**
     * @inheritDoc
     */
    public function quoteTableName(string $tableName): string
    {
        return $this->getAdapter()->quoteTableName($tableName);
    }

    /**
     * @inheritDoc
     */
    public function quoteColumnName(string $columnName): string
    {
        return $this->getAdapter()->quoteColumnName($columnName);
    }

    /**
     * @inheritDoc
     */
    public function hasTable(string $tableName): bool
    {
        return $this->getAdapter()->hasTable($tableName);
    }

    /**
     * @inheritDoc
     */
    public function createTable(Table $table, array $columns = [], array $indexes = []): void
    {
        $this->getAdapter()->createTable($table, $columns, $indexes);
    }

    /**
     * @inheritDoc
     */
    public function getColumns(string $tableName): array
    {
        return $this->getAdapter()->getColumns($tableName);
    }

    /**
     * @inheritDoc
     */
    public function hasColumn(string $tableName, string $columnName): bool
    {
        return $this->getAdapter()->hasColumn($tableName, $columnName);
    }

    /**
     * @inheritDoc
     */
    public function hasIndex(string $tableName, $columns): bool
    {
        return $this->getAdapter()->hasIndex($tableName, $columns);
    }

    /**
     * @inheritDoc
     */
    public function hasIndexByName(string $tableName, string $indexName): bool
    {
        return $this->getAdapter()->hasIndexByName($tableName, $indexName);
    }

    /**
     * @inheritDoc
     */
    public function hasPrimaryKey(string $tableName, $columns, ?string $constraint = null): bool
    {
        return $this->getAdapter()->hasPrimaryKey($tableName, $columns, $constraint);
    }

    /**
     * @inheritDoc
     */
    public function hasForeignKey(string $tableName, $columns, ?string $constraint = null): bool
    {
        return $this->getAdapter()->hasForeignKey($tableName, $columns, $constraint);
    }

    /**
     * @inheritDoc
     */
    public function getSqlType($type, ?int $limit = null): array
    {
        return $this->getAdapter()->getSqlType($type, $limit);
    }

    /**
     * @inheritDoc
     */
    public function createDatabase(string $name, array $options = []): void
    {
        $this->getAdapter()->createDatabase($name, $options);
    }

    /**
     * @inheritDoc
     */
    public function hasDatabase(string $name): bool
    {
        return $this->getAdapter()->hasDatabase($name);
    }

    /**
     * @inheritDoc
     */
    public function dropDatabase(string $name): void
    {
        $this->getAdapter()->dropDatabase($name);
    }

    /**
     * @inheritDoc
     */
    public function createSchema(string $schemaName = 'public'): void
    {
        $this->getAdapter()->createSchema($schemaName);
    }

    /**
     * @inheritDoc
     */
    public function dropSchema(string $schemaName): void
    {
        $this->getAdapter()->dropSchema($schemaName);
    }

    /**
     * @inheritDoc
     */
    public function truncateTable(string $tableName): void
    {
        $this->getAdapter()->truncateTable($tableName);
    }

    /**
     * @inheritDoc
     */
    public function castToBool($value)
    {
        return $this->getAdapter()->castToBool($value);
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->getAdapter()->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function executeActions(Table $table, array $actions): void
    {
        $this->getAdapter()->executeActions($table, $actions);
    }

    /**
     * @inheritDoc
     */
    public function getQueryBuilder(): Query
    {
        return $this->getAdapter()->getQueryBuilder();
    }
}
