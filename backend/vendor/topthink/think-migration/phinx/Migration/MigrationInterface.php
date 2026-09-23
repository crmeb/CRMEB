<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Migration;

use Cake\Database\Query;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Table;
use think\console\Input as InputInterface;
use think\console\Output as OutputInterface;

/**
 * Migration interface
 *
 * @author Rob Morgan <robbym@gmail.com>
 */
interface MigrationInterface
{
    /**
     * @var string
     */
    public const CHANGE = 'change';

    /**
     * @var string
     */
    public const UP = 'up';

    /**
     * @var string
     */
    public const DOWN = 'down';

    /**
     * @var string
     */
    public const INIT = 'init';

    /**
     * Sets the database adapter.
     *
     * @param \Phinx\Db\Adapter\AdapterInterface $adapter Database Adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter);

    /**
     * Gets the database adapter.
     *
     * @return \Phinx\Db\Adapter\AdapterInterface|null
     */
    public function getAdapter(): ?AdapterInterface;

    /**
     * Sets the input object to be used in migration object
     *
     * @param \think\console\Input $input Input
     * @return $this
     */
    public function setInput(InputInterface $input);

    /**
     * Gets the input object to be used in migration object
     *
     * @return \think\console\Input|null
     */
    public function getInput(): ?InputInterface;

    /**
     * Sets the output object to be used in migration object
     *
     * @param \think\console\Output $output Output
     * @return $this
     */
    public function setOutput(OutputInterface $output);

    /**
     * Gets the output object to be used in migration object
     *
     * @return \think\console\Output|null
     */
    public function getOutput(): ?OutputInterface;

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the detected environment
     *
     * @return string
     */
    public function getEnvironment(): string;

    /**
     * Sets the migration version number.
     *
     * @param int $version Version
     * @return $this
     */
    public function setVersion(int $version);

    /**
     * Gets the migration version number.
     *
     * @return int
     */
    public function getVersion(): int;

    /**
     * Sets whether this migration is being applied or reverted
     *
     * @param bool $isMigratingUp True if the migration is being applied
     * @return $this
     */
    public function setMigratingUp(bool $isMigratingUp);

    /**
     * Gets whether this migration is being applied or reverted.
     * True means that the migration is being applied.
     *
     * @return bool
     */
    public function isMigratingUp(): bool;

    /**
     * Executes a SQL statement and returns the number of affected rows.
     *
     * @param string $sql SQL
     * @param array $params parameters to use for prepared query
     * @return int
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Executes a SQL statement.
     *
     * The return type depends on the underlying adapter being used. To improve
     * IDE auto-completion possibility, you can overwrite the query method
     * phpDoc in your (typically custom abstract parent) migration class, where
     * you can set the return type by the adapter in your current use.
     *
     * @param string $sql SQL
     * @param array $params parameters to use for prepared query
     * @return mixed
     */
    public function query(string $sql, array $params = []);

    /**
     * Returns a new Query object that can be used to build complex SELECT, UPDATE, INSERT or DELETE
     * queries and execute them against the current database.
     *
     * Queries executed through the query builder are always sent to the database, regardless of the
     * the dry-run settings.
     *
     * @see https://api.cakephp.org/3.6/class-Cake.Database.Query.html
     * @return \Cake\Database\Query
     */
    public function getQueryBuilder(): Query;

    /**
     * Executes a query and returns only one row as an array.
     *
     * @param string $sql SQL
     * @return array|false
     */
    public function fetchRow(string $sql);

    /**
     * Executes a query and returns an array of rows.
     *
     * @param string $sql SQL
     * @return array
     */
    public function fetchAll(string $sql): array;

    /**
     * Create a new database.
     *
     * @param string $name Database Name
     * @param array<string, mixed> $options Options
     * @return void
     */
    public function createDatabase(string $name, array $options): void;

    /**
     * Drop a database.
     *
     * @param string $name Database Name
     * @return void
     */
    public function dropDatabase(string $name): void;

    /**
     * Creates schema.
     *
     * This will thrown an error for adapters that do not support schemas.
     *
     * @param string $name Schema name
     * @return void
     * @throws \BadMethodCallException
     */
    public function createSchema(string $name): void;

    /**
     * Drops schema.
     *
     * This will thrown an error for adapters that do not support schemas.
     *
     * @param string $name Schema name
     * @return void
     * @throws \BadMethodCallException
     */
    public function dropSchema(string $name): void;

    /**
     * Checks to see if a table exists.
     *
     * @param string $tableName Table name
     * @return bool
     */
    public function hasTable(string $tableName): bool;

    /**
     * Returns an instance of the <code>\Table</code> class.
     *
     * You can use this class to create and manipulate tables.
     *
     * @param string $tableName Table name
     * @param array<string, mixed> $options Options
     * @return \Phinx\Db\Table
     */
    public function table(string $tableName, array $options): Table;

    /**
     * Perform checks on the migration, printing a warning
     * if there are potential problems.
     *
     * @return void
     */
    public function preFlightCheck(): void;

    /**
     * Perform checks on the migration after completion
     *
     * Right now, the only check is whether all changes were committed
     *
     * @return void
     */
    public function postFlightCheck(): void;

    /**
     * Checks to see if the migration should be executed.
     *
     * Returns true by default.
     *
     * You can use this to prevent a migration from executing.
     *
     * @return bool
     */
    public function shouldExecute(): bool;
}
