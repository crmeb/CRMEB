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
 * Adapter Interface.
 *
 * @author Rob Morgan <robbym@gmail.com>
 * @method \PDO getConnection()
 */
interface AdapterInterface
{
    public const PHINX_TYPE_STRING = 'string';
    public const PHINX_TYPE_CHAR = 'char';
    public const PHINX_TYPE_TEXT = 'text';
    public const PHINX_TYPE_INTEGER = 'integer';
    public const PHINX_TYPE_TINY_INTEGER = 'tinyinteger';
    public const PHINX_TYPE_SMALL_INTEGER = 'smallinteger';
    public const PHINX_TYPE_BIG_INTEGER = 'biginteger';
    public const PHINX_TYPE_BIT = 'bit';
    public const PHINX_TYPE_FLOAT = 'float';
    public const PHINX_TYPE_DECIMAL = 'decimal';
    public const PHINX_TYPE_DOUBLE = 'double';
    public const PHINX_TYPE_DATETIME = 'datetime';
    public const PHINX_TYPE_TIMESTAMP = 'timestamp';
    public const PHINX_TYPE_TIME = 'time';
    public const PHINX_TYPE_DATE = 'date';
    public const PHINX_TYPE_BINARY = 'binary';
    public const PHINX_TYPE_VARBINARY = 'varbinary';
    public const PHINX_TYPE_BINARYUUID = 'binaryuuid';
    public const PHINX_TYPE_BLOB = 'blob';
    public const PHINX_TYPE_TINYBLOB = 'tinyblob'; // Specific to Mysql.
    public const PHINX_TYPE_MEDIUMBLOB = 'mediumblob'; // Specific to Mysql
    public const PHINX_TYPE_LONGBLOB = 'longblob'; // Specific to Mysql
    public const PHINX_TYPE_BOOLEAN = 'boolean';
    public const PHINX_TYPE_JSON = 'json';
    public const PHINX_TYPE_JSONB = 'jsonb';
    public const PHINX_TYPE_UUID = 'uuid';
    public const PHINX_TYPE_FILESTREAM = 'filestream';

    // Geospatial database types
    public const PHINX_TYPE_GEOMETRY = 'geometry';
    public const PHINX_TYPE_GEOGRAPHY = 'geography';
    public const PHINX_TYPE_POINT = 'point';
    public const PHINX_TYPE_LINESTRING = 'linestring';
    public const PHINX_TYPE_POLYGON = 'polygon';

    public const PHINX_TYPES_GEOSPATIAL = [
        self::PHINX_TYPE_GEOMETRY,
        self::PHINX_TYPE_POINT,
        self::PHINX_TYPE_LINESTRING,
        self::PHINX_TYPE_POLYGON,
    ];

    // only for mysql so far
    public const PHINX_TYPE_MEDIUM_INTEGER = 'mediuminteger';
    public const PHINX_TYPE_ENUM = 'enum';
    public const PHINX_TYPE_SET = 'set';
    public const PHINX_TYPE_YEAR = 'year';

    // only for postgresql so far
    public const PHINX_TYPE_CIDR = 'cidr';
    public const PHINX_TYPE_INET = 'inet';
    public const PHINX_TYPE_MACADDR = 'macaddr';
    public const PHINX_TYPE_INTERVAL = 'interval';

    /**
     * Get all migrated version numbers.
     *
     * @return array<int>
     */
    public function getVersions(): array;

    /**
     * Get all migration log entries, indexed by version creation time and sorted ascendingly by the configuration's
     * version order option
     *
     * @return array<int, mixed>
     */
    public function getVersionLog(): array;

    /**
     * Set adapter configuration options.
     *
     * @param array<string, mixed> $options Options
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Get all adapter options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * Check if an option has been set.
     *
     * @param string $name Name
     * @return bool
     */
    public function hasOption(string $name): bool;

    /**
     * Get a single adapter option, or null if the option does not exist.
     *
     * @param string $name Name
     * @return mixed
     */
    public function getOption(string $name);

    /**
     * Sets the console input.
     *
     * @param \think\console\Input $input Input
     * @return $this
     */
    public function setInput(InputInterface $input);

    /**
     * Gets the console input.
     *
     * @return \think\console\Input|null
     */
    public function getInput(): ?InputInterface;

    /**
     * Sets the console output.
     *
     * @param \think\console\Output $output Output
     * @return $this
     */
    public function setOutput(OutputInterface $output);

    /**
     * Gets the console output.
     *
     * @return \think\console\Output
     */
    public function getOutput(): OutputInterface;

    /**
     * Returns a new Phinx\Db\Table\Column using the existent data domain.
     *
     * @param string $columnName The desired column name
     * @param string $type The type for the column. Can be a data domain type.
     * @param array<string, mixed> $options Options array
     * @return \Phinx\Db\Table\Column
     */
    public function getColumnForType(string $columnName, string $type, array $options): Column;

    /**
     * Records a migration being run.
     *
     * @param \Phinx\Migration\MigrationInterface $migration Migration
     * @param string $direction Direction
     * @param string $startTime Start Time
     * @param string $endTime End Time
     * @return $this
     */
    public function migrated(MigrationInterface $migration, string $direction, string $startTime, string $endTime);

    /**
     * Toggle a migration breakpoint.
     *
     * @param \Phinx\Migration\MigrationInterface $migration Migration
     * @return $this
     */
    public function toggleBreakpoint(MigrationInterface $migration);

    /**
     * Reset all migration breakpoints.
     *
     * @return int The number of breakpoints reset
     */
    public function resetAllBreakpoints(): int;

    /**
     * Set a migration breakpoint.
     *
     * @param \Phinx\Migration\MigrationInterface $migration The migration target for the breakpoint set
     * @return $this
     */
    public function setBreakpoint(MigrationInterface $migration);

    /**
     * Unset a migration breakpoint.
     *
     * @param \Phinx\Migration\MigrationInterface $migration The migration target for the breakpoint unset
     * @return $this
     */
    public function unsetBreakpoint(MigrationInterface $migration);

    /**
     * Creates the schema table.
     *
     * @return void
     */
    public function createSchemaTable(): void;

    /**
     * Returns the adapter type.
     *
     * @return string
     */
    public function getAdapterType(): string;

    /**
     * Initializes the database connection.
     *
     * @throws \RuntimeException When the requested database driver is not installed.
     * @return void
     */
    public function connect(): void;

    /**
     * Closes the database connection.
     *
     * @return void
     */
    public function disconnect(): void;

    /**
     * Does the adapter support transactions?
     *
     * @return bool
     */
    public function hasTransactions(): bool;

    /**
     * Begin a transaction.
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commitTransaction(): void;

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    public function rollbackTransaction(): void;

    /**
     * Executes a SQL statement and returns the number of affected rows.
     *
     * @param string $sql SQL
     * @param array $params parameters to use for prepared query
     * @return int
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Executes a list of migration actions for the given table
     *
     * @param \Phinx\Db\Table\Table $table The table to execute the actions for
     * @param \Phinx\Db\Action\Action[] $actions The table to execute the actions for
     * @return void
     */
    public function executeActions(Table $table, array $actions): void;

    /**
     * Returns a new Query object
     *
     * @return \Cake\Database\Query
     */
    public function getQueryBuilder(): Query;

    /**
     * Executes a SQL statement.
     *
     * The return type depends on the underlying adapter being used.
     *
     * @param string $sql SQL
     * @param array $params parameters to use for prepared query
     * @return mixed
     */
    public function query(string $sql, array $params = []);

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
     * Inserts data into a table.
     *
     * @param \Phinx\Db\Table\Table $table Table where to insert data
     * @param array $row Row
     * @return void
     */
    public function insert(Table $table, array $row): void;

    /**
     * Inserts data into a table in a bulk.
     *
     * @param \Phinx\Db\Table\Table $table Table where to insert data
     * @param array $rows Rows
     * @return void
     */
    public function bulkinsert(Table $table, array $rows): void;

    /**
     * Quotes a table name for use in a query.
     *
     * @param string $tableName Table name
     * @return string
     */
    public function quoteTableName(string $tableName): string;

    /**
     * Quotes a column name for use in a query.
     *
     * @param string $columnName Table name
     * @return string
     */
    public function quoteColumnName(string $columnName): string;

    /**
     * Checks to see if a table exists.
     *
     * @param string $tableName Table name
     * @return bool
     */
    public function hasTable(string $tableName): bool;

    /**
     * Creates the specified database table.
     *
     * @param \Phinx\Db\Table\Table $table Table
     * @param \Phinx\Db\Table\Column[] $columns List of columns in the table
     * @param \Phinx\Db\Table\Index[] $indexes List of indexes for the table
     * @return void
     */
    public function createTable(Table $table, array $columns = [], array $indexes = []): void;

    /**
     * Truncates the specified table
     *
     * @param string $tableName Table name
     * @return void
     */
    public function truncateTable(string $tableName): void;

    /**
     * Returns table columns
     *
     * @param string $tableName Table name
     * @return \Phinx\Db\Table\Column[]
     */
    public function getColumns(string $tableName): array;

    /**
     * Checks to see if a column exists.
     *
     * @param string $tableName Table name
     * @param string $columnName Column name
     * @return bool
     */
    public function hasColumn(string $tableName, string $columnName): bool;

    /**
     * Checks to see if an index exists.
     *
     * @param string $tableName Table name
     * @param string|string[] $columns Column(s)
     * @return bool
     */
    public function hasIndex(string $tableName, $columns): bool;

    /**
     * Checks to see if an index specified by name exists.
     *
     * @param string $tableName Table name
     * @param string $indexName Index name
     * @return bool
     */
    public function hasIndexByName(string $tableName, string $indexName): bool;

    /**
     * Checks to see if the specified primary key exists.
     *
     * @param string $tableName Table name
     * @param string|string[] $columns Column(s)
     * @param string|null $constraint Constraint name
     * @return bool
     */
    public function hasPrimaryKey(string $tableName, $columns, ?string $constraint = null): bool;

    /**
     * Checks to see if a foreign key exists.
     *
     * @param string $tableName Table name
     * @param string|string[] $columns Column(s)
     * @param string|null $constraint Constraint name
     * @return bool
     */
    public function hasForeignKey(string $tableName, $columns, ?string $constraint = null): bool;

    /**
     * Returns an array of the supported Phinx column types.
     *
     * @return string[]
     */
    public function getColumnTypes(): array;

    /**
     * Checks that the given column is of a supported type.
     *
     * @param \Phinx\Db\Table\Column $column Column
     * @return bool
     */
    public function isValidColumnType(Column $column): bool;

    /**
     * Converts the Phinx logical type to the adapter's SQL type.
     *
     * @param \Phinx\Util\Literal|string $type Type
     * @param int|null $limit Limit
     * @return array
     */
    public function getSqlType($type, ?int $limit = null): array;

    /**
     * Creates a new database.
     *
     * @param string $name Database Name
     * @param array<string, mixed> $options Options
     * @return void
     */
    public function createDatabase(string $name, array $options = []): void;

    /**
     * Checks to see if a database exists.
     *
     * @param string $name Database Name
     * @return bool
     */
    public function hasDatabase(string $name): bool;

    /**
     * Drops the specified database.
     *
     * @param string $name Database Name
     * @return void
     */
    public function dropDatabase(string $name): void;

    /**
     * Creates the specified schema or throws an exception
     * if there is no support for it.
     *
     * @param string $schemaName Schema Name
     * @return void
     */
    public function createSchema(string $schemaName = 'public'): void;

    /**
     * Drops the specified schema table or throws an exception
     * if there is no support for it.
     *
     * @param string $schemaName Schema name
     * @return void
     */
    public function dropSchema(string $schemaName): void;

    /**
     * Cast a value to a boolean appropriate for the adapter.
     *
     * @param mixed $value The value to be cast
     * @return mixed
     */
    public function castToBool($value);
}
