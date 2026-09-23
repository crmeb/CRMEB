<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql as MysqlDriver;
use InvalidArgumentException;
use PDO;
use Phinx\Config\FeatureFlags;
use Phinx\Db\Table\Column;
use Phinx\Db\Table\ForeignKey;
use Phinx\Db\Table\Index;
use Phinx\Db\Table\Table;
use Phinx\Db\Util\AlterInstructions;
use Phinx\Util\Literal;
use RuntimeException;

/**
 * Phinx MySQL Adapter.
 *
 * @author Rob Morgan <robbym@gmail.com>
 */
class MysqlAdapter extends PdoAdapter
{
    /**
     * @var string[]
     */
    protected static $specificColumnTypes = [
        self::PHINX_TYPE_ENUM,
        self::PHINX_TYPE_SET,
        self::PHINX_TYPE_YEAR,
        self::PHINX_TYPE_JSON,
        self::PHINX_TYPE_BINARYUUID,
        self::PHINX_TYPE_TINYBLOB,
        self::PHINX_TYPE_MEDIUMBLOB,
        self::PHINX_TYPE_LONGBLOB,
        self::PHINX_TYPE_MEDIUM_INTEGER,
    ];

    /**
     * @var bool[]
     */
    protected $signedColumnTypes = [
        self::PHINX_TYPE_INTEGER => true,
        self::PHINX_TYPE_TINY_INTEGER => true,
        self::PHINX_TYPE_SMALL_INTEGER => true,
        self::PHINX_TYPE_MEDIUM_INTEGER => true,
        self::PHINX_TYPE_BIG_INTEGER => true,
        self::PHINX_TYPE_FLOAT => true,
        self::PHINX_TYPE_DECIMAL => true,
        self::PHINX_TYPE_DOUBLE => true,
        self::PHINX_TYPE_BOOLEAN => true,
    ];

    // These constants roughly correspond to the maximum allowed value for each field,
    // except for the `_LONG` and `_BIG` variants, which are maxed at 32-bit
    // PHP_INT_MAX value. The `INT_REGULAR` field is just arbitrarily half of INT_BIG
    // as its actual value is its regular value is larger than PHP_INT_MAX. We do this
    // to keep consistent the type hints for getSqlType and Column::$limit being integers.
    public const TEXT_TINY = 255;
    public const TEXT_SMALL = 255; /* deprecated, alias of TEXT_TINY */
    public const TEXT_REGULAR = 65535;
    public const TEXT_MEDIUM = 16777215;
    public const TEXT_LONG = 2147483647;

    // According to https://dev.mysql.com/doc/refman/5.0/en/blob.html BLOB sizes are the same as TEXT
    public const BLOB_TINY = 255;
    public const BLOB_SMALL = 255; /* deprecated, alias of BLOB_TINY */
    public const BLOB_REGULAR = 65535;
    public const BLOB_MEDIUM = 16777215;
    public const BLOB_LONG = 2147483647;

    public const INT_TINY = 255;
    public const INT_SMALL = 65535;
    public const INT_MEDIUM = 16777215;
    public const INT_REGULAR = 1073741823;
    public const INT_BIG = 2147483647;

    public const INT_DISPLAY_TINY = 4;
    public const INT_DISPLAY_SMALL = 6;
    public const INT_DISPLAY_MEDIUM = 8;
    public const INT_DISPLAY_REGULAR = 11;
    public const INT_DISPLAY_BIG = 20;

    public const BIT = 64;

    public const TYPE_YEAR = 'year';

    public const FIRST = 'FIRST';

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    public function connect(): void
    {
        if ($this->connection === null) {
            if (!class_exists('PDO') || !in_array('mysql', PDO::getAvailableDrivers(), true)) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException('You need to enable the PDO_Mysql extension for Phinx to run properly.');
                // @codeCoverageIgnoreEnd
            }

            $options = $this->getOptions();

            $dsn = 'mysql:';

            if (!empty($options['unix_socket'])) {
                // use socket connection
                $dsn .= 'unix_socket=' . $options['unix_socket'];
            } else {
                // use network connection
                $dsn .= 'host=' . $options['host'];
                if (!empty($options['port'])) {
                    $dsn .= ';port=' . $options['port'];
                }
            }

            $dsn .= ';dbname=' . $options['name'];

            // charset support
            if (!empty($options['charset'])) {
                $dsn .= ';charset=' . $options['charset'];
            }

            $driverOptions = [];

            // use custom data fetch mode
            if (!empty($options['fetch_mode'])) {
                $driverOptions[PDO::ATTR_DEFAULT_FETCH_MODE] = constant('\PDO::FETCH_' . strtoupper($options['fetch_mode']));
            }

            // pass \PDO::ATTR_PERSISTENT to driver options instead of useless setting it after instantiation
            if (isset($options['attr_persistent'])) {
                $driverOptions[PDO::ATTR_PERSISTENT] = $options['attr_persistent'];
            }

            // support arbitrary \PDO::MYSQL_ATTR_* driver options and pass them to PDO
            // https://php.net/manual/en/ref.pdo-mysql.php#pdo-mysql.constants
            foreach ($options as $key => $option) {
                if (strpos($key, 'mysql_attr_') === 0) {
                    $pdoConstant = '\PDO::' . strtoupper($key);
                    if (!defined($pdoConstant)) {
                        throw new \UnexpectedValueException('Invalid PDO attribute: ' . $key . ' (' . $pdoConstant . ')');
                    }
                    $driverOptions[constant($pdoConstant)] = $option;
                }
            }

            $db = $this->createPdoConnection($dsn, $options['user'] ?? null, $options['pass'] ?? null, $driverOptions);

            $this->setConnection($db);
        }
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * @inheritDoc
     */
    public function hasTransactions(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        $this->execute('START TRANSACTION');
    }

    /**
     * @inheritDoc
     */
    public function commitTransaction(): void
    {
        $this->execute('COMMIT');
    }

    /**
     * @inheritDoc
     */
    public function rollbackTransaction(): void
    {
        $this->execute('ROLLBACK');
    }

    /**
     * @inheritDoc
     */
    public function quoteTableName(string $tableName): string
    {
        return str_replace('.', '`.`', $this->quoteColumnName($tableName));
    }

    /**
     * @inheritDoc
     */
    public function quoteColumnName(string $columnName): string
    {
        return '`' . str_replace('`', '``', $columnName) . '`';
    }

    /**
     * @inheritDoc
     */
    public function hasTable(string $tableName): bool
    {
        if ($this->hasCreatedTable($tableName)) {
            return true;
        }

        if (strpos($tableName, '.') !== false) {
            [$schema, $table] = explode('.', $tableName);
            $exists = $this->hasTableWithSchema($schema, $table);
            // Only break here on success, because it is possible for table names to contain a dot.
            if ($exists) {
                return true;
            }
        }

        $options = $this->getOptions();

        return $this->hasTableWithSchema($options['name'], $tableName);
    }

    /**
     * @param string $schema The table schema
     * @param string $tableName The table name
     * @return bool
     */
    protected function hasTableWithSchema(string $schema, string $tableName): bool
    {
        $result = $this->fetchRow(sprintf(
            "SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = '%s'",
            $schema,
            $tableName
        ));

        return !empty($result);
    }

    /**
     * @inheritDoc
     */
    public function createTable(Table $table, array $columns = [], array $indexes = []): void
    {
        // This method is based on the MySQL docs here: https://dev.mysql.com/doc/refman/5.1/en/create-index.html
        $defaultOptions = [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        $options = array_merge(
            $defaultOptions,
            array_intersect_key($this->getOptions(), $defaultOptions),
            $table->getOptions()
        );

        // Add the default primary key
        if (!isset($options['id']) || (isset($options['id']) && $options['id'] === true)) {
            $options['id'] = 'id';
        }

        if (isset($options['id']) && is_string($options['id'])) {
            // Handle id => "field_name" to support AUTO_INCREMENT
            $column = new Column();
            $column->setName($options['id'])
                   ->setType('integer')
                   ->setOptions([
                       'signed' => $options['signed'] ?? !FeatureFlags::$unsignedPrimaryKeys,
                       'identity' => true,
                   ]);

            if (isset($options['limit'])) {
                $column->setLimit($options['limit']);
            }

            array_unshift($columns, $column);
            if (isset($options['primary_key']) && (array)$options['id'] !== (array)$options['primary_key']) {
                throw new InvalidArgumentException('You cannot enable an auto incrementing ID field and a primary key');
            }
            $options['primary_key'] = $options['id'];
        }

        // open: process table options like collation etc

        // process table engine (default to InnoDB)
        $optionsStr = 'ENGINE = InnoDB';
        if (isset($options['engine'])) {
            $optionsStr = sprintf('ENGINE = %s', $options['engine']);
        }

        // process table collation
        if (isset($options['collation'])) {
            $charset = explode('_', $options['collation']);
            $optionsStr .= sprintf(' CHARACTER SET %s', $charset[0]);
            $optionsStr .= sprintf(' COLLATE %s', $options['collation']);
        }

        // set the table comment
        if (isset($options['comment'])) {
            $optionsStr .= sprintf(' COMMENT=%s ', $this->getConnection()->quote($options['comment']));
        }

        // set the table row format
        if (isset($options['row_format'])) {
            $optionsStr .= sprintf(' ROW_FORMAT=%s ', $options['row_format']);
        }

        $sql = 'CREATE TABLE ';
        $sql .= $this->quoteTableName($table->getName()) . ' (';
        foreach ($columns as $column) {
            $sql .= $this->quoteColumnName($column->getName()) . ' ' . $this->getColumnSqlDefinition($column) . ', ';
        }

        // set the primary key(s)
        if (isset($options['primary_key'])) {
            $sql = rtrim($sql);
            $sql .= ' PRIMARY KEY (';
            if (is_string($options['primary_key'])) { // handle primary_key => 'id'
                $sql .= $this->quoteColumnName($options['primary_key']);
            } elseif (is_array($options['primary_key'])) { // handle primary_key => array('tag_id', 'resource_id')
                $sql .= implode(',', array_map([$this, 'quoteColumnName'], (array)$options['primary_key']));
            }
            $sql .= ')';
        } else {
            $sql = substr(rtrim($sql), 0, -1); // no primary keys
        }

        // set the indexes
        foreach ($indexes as $index) {
            $sql .= ', ' . $this->getIndexSqlDefinition($index);
        }

        $sql .= ') ' . $optionsStr;
        $sql = rtrim($sql);

        // execute the sql
        $this->execute($sql);

        $this->addCreatedTable($table->getName());
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function getChangePrimaryKeyInstructions(Table $table, $newColumns): AlterInstructions
    {
        $instructions = new AlterInstructions();

        // Drop the existing primary key
        $primaryKey = $this->getPrimaryKey($table->getName());
        if (!empty($primaryKey['columns'])) {
            $instructions->addAlter('DROP PRIMARY KEY');
        }

        // Add the primary key(s)
        if (!empty($newColumns)) {
            $sql = 'ADD PRIMARY KEY (';
            if (is_string($newColumns)) { // handle primary_key => 'id'
                $sql .= $this->quoteColumnName($newColumns);
            } elseif (is_array($newColumns)) { // handle primary_key => array('tag_id', 'resource_id')
                $sql .= implode(',', array_map([$this, 'quoteColumnName'], $newColumns));
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value for primary key: %s',
                    json_encode($newColumns)
                ));
            }
            $sql .= ')';
            $instructions->addAlter($sql);
        }

        return $instructions;
    }

    /**
     * @inheritDoc
     */
    protected function getChangeCommentInstructions(Table $table, ?string $newComment): AlterInstructions
    {
        $instructions = new AlterInstructions();

        // passing 'null' is to remove table comment
        $newComment = $newComment ?? '';
        $sql = sprintf(' COMMENT=%s ', $this->getConnection()->quote($newComment));
        $instructions->addAlter($sql);

        return $instructions;
    }

    /**
     * @inheritDoc
     */
    protected function getRenameTableInstructions(string $tableName, string $newTableName): AlterInstructions
    {
        $this->updateCreatedTableName($tableName, $newTableName);
        $sql = sprintf(
            'RENAME TABLE %s TO %s',
            $this->quoteTableName($tableName),
            $this->quoteTableName($newTableName)
        );

        return new AlterInstructions([], [$sql]);
    }

    /**
     * @inheritDoc
     */
    protected function getDropTableInstructions(string $tableName): AlterInstructions
    {
        $this->removeCreatedTable($tableName);
        $sql = sprintf('DROP TABLE %s', $this->quoteTableName($tableName));

        return new AlterInstructions([], [$sql]);
    }

    /**
     * @inheritDoc
     */
    public function truncateTable(string $tableName): void
    {
        $sql = sprintf(
            'TRUNCATE TABLE %s',
            $this->quoteTableName($tableName)
        );

        $this->execute($sql);
    }

    /**
     * @inheritDoc
     */
    public function getColumns(string $tableName): array
    {
        $columns = [];
        $rows = $this->fetchAll(sprintf('SHOW COLUMNS FROM %s', $this->quoteTableName($tableName)));
        foreach ($rows as $columnInfo) {
            $phinxType = $this->getPhinxType($columnInfo['Type']);

            $column = new Column();
            $column->setName($columnInfo['Field'])
                   ->setNull($columnInfo['Null'] !== 'NO')
                   ->setType($phinxType['name'])
                   ->setSigned(strpos($columnInfo['Type'], 'unsigned') === false)
                   ->setLimit($phinxType['limit'])
                   ->setScale($phinxType['scale']);

            if ($columnInfo['Extra'] === 'auto_increment') {
                $column->setIdentity(true);
            }

            if (isset($phinxType['values'])) {
                $column->setValues($phinxType['values']);
            }

            $default = $columnInfo['Default'];
            if (
                is_string($default) &&
                in_array(
                    $column->getType(),
                    array_merge(
                        static::PHINX_TYPES_GEOSPATIAL,
                        [static::PHINX_TYPE_BLOB, static::PHINX_TYPE_JSON, static::PHINX_TYPE_TEXT]
                    )
                )
            ) {
                // The default that comes back from MySQL for these types prefixes the collation type and
                // surrounds the value with escaped single quotes, for example "_utf8mbf4\'abc\'", and so
                // this converts that then down to the default value of "abc" to correspond to what the user
                // would have specified in a migration.
                $default = preg_replace("/^_(?:[a-zA-Z0-9]+?)\\\'(.*)\\\'$/", '\1', $default);
            }
            $column->setDefault($default);

            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public function hasColumn(string $tableName, string $columnName): bool
    {
        $rows = $this->fetchAll(sprintf('SHOW COLUMNS FROM %s', $this->quoteTableName($tableName)));
        foreach ($rows as $column) {
            if (strcasecmp($column['Field'], $columnName) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function getAddColumnInstructions(Table $table, Column $column): AlterInstructions
    {
        $alter = sprintf(
            'ADD %s %s',
            $this->quoteColumnName($column->getName()),
            $this->getColumnSqlDefinition($column)
        );

        $alter .= $this->afterClause($column);

        return new AlterInstructions([$alter]);
    }

    /**
     * Exposes the MySQL syntax to arrange a column `FIRST`.
     *
     * @param \Phinx\Db\Table\Column $column The column being altered.
     * @return string The appropriate SQL fragment.
     */
    protected function afterClause(Column $column): string
    {
        $after = $column->getAfter();
        if (empty($after)) {
            return '';
        }

        if ($after === self::FIRST) {
            return ' FIRST';
        }

        return ' AFTER ' . $this->quoteColumnName($after);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function getRenameColumnInstructions(string $tableName, string $columnName, string $newColumnName): AlterInstructions
    {
        $rows = $this->fetchAll(sprintf('SHOW FULL COLUMNS FROM %s', $this->quoteTableName($tableName)));

        foreach ($rows as $row) {
            if (strcasecmp($row['Field'], $columnName) === 0) {
                $null = $row['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
                $comment = isset($row['Comment']) ? ' COMMENT ' . '\'' . addslashes($row['Comment']) . '\'' : '';
                $extra = ' ' . strtoupper($row['Extra']);
                if (($row['Default'] !== null)) {
                    $extra .= $this->getDefaultValueDefinition($row['Default']);
                }
                $definition = $row['Type'] . ' ' . $null . $extra . $comment;

                $alter = sprintf(
                    'CHANGE COLUMN %s %s %s',
                    $this->quoteColumnName($columnName),
                    $this->quoteColumnName($newColumnName),
                    $definition
                );

                return new AlterInstructions([$alter]);
            }
        }

        throw new InvalidArgumentException(sprintf(
            "The specified column doesn't exist: " .
            $columnName
        ));
    }

    /**
     * @inheritDoc
     */
    protected function getChangeColumnInstructions(string $tableName, string $columnName, Column $newColumn): AlterInstructions
    {
        $alter = sprintf(
            'CHANGE %s %s %s%s',
            $this->quoteColumnName($columnName),
            $this->quoteColumnName($newColumn->getName()),
            $this->getColumnSqlDefinition($newColumn),
            $this->afterClause($newColumn)
        );

        return new AlterInstructions([$alter]);
    }

    /**
     * @inheritDoc
     */
    protected function getDropColumnInstructions(string $tableName, string $columnName): AlterInstructions
    {
        $alter = sprintf('DROP COLUMN %s', $this->quoteColumnName($columnName));

        return new AlterInstructions([$alter]);
    }

    /**
     * Get an array of indexes from a particular table.
     *
     * @param string $tableName Table name
     * @return array
     */
    protected function getIndexes(string $tableName): array
    {
        $indexes = [];
        $rows = $this->fetchAll(sprintf('SHOW INDEXES FROM %s', $this->quoteTableName($tableName)));
        foreach ($rows as $row) {
            if (!isset($indexes[$row['Key_name']])) {
                $indexes[$row['Key_name']] = ['columns' => []];
            }
            $indexes[$row['Key_name']]['columns'][] = strtolower($row['Column_name']);
        }

        return $indexes;
    }

    /**
     * @inheritDoc
     */
    public function hasIndex(string $tableName, $columns): bool
    {
        if (is_string($columns)) {
            $columns = [$columns]; // str to array
        }

        $columns = array_map('strtolower', $columns);
        $indexes = $this->getIndexes($tableName);

        foreach ($indexes as $index) {
            if ($columns == $index['columns']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasIndexByName(string $tableName, string $indexName): bool
    {
        $indexes = $this->getIndexes($tableName);

        foreach ($indexes as $name => $index) {
            if ($name === $indexName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function getAddIndexInstructions(Table $table, Index $index): AlterInstructions
    {
        $instructions = new AlterInstructions();

        if ($index->getType() === Index::FULLTEXT) {
            // Must be executed separately
            // SQLSTATE[HY000]: General error: 1795 InnoDB presently supports one FULLTEXT index creation at a time
            $alter = sprintf(
                'ALTER TABLE %s ADD %s',
                $this->quoteTableName($table->getName()),
                $this->getIndexSqlDefinition($index)
            );

            $instructions->addPostStep($alter);
        } else {
            $alter = sprintf(
                'ADD %s',
                $this->getIndexSqlDefinition($index)
            );

            $instructions->addAlter($alter);
        }

        return $instructions;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function getDropIndexByColumnsInstructions(string $tableName, $columns): AlterInstructions
    {
        if (is_string($columns)) {
            $columns = [$columns]; // str to array
        }

        $indexes = $this->getIndexes($tableName);
        $columns = array_map('strtolower', $columns);

        foreach ($indexes as $indexName => $index) {
            if ($columns == $index['columns']) {
                return new AlterInstructions([sprintf(
                    'DROP INDEX %s',
                    $this->quoteColumnName($indexName)
                )]);
            }
        }

        throw new InvalidArgumentException(sprintf(
            "The specified index on columns '%s' does not exist",
            implode(',', $columns)
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function getDropIndexByNameInstructions(string $tableName, $indexName): AlterInstructions
    {
        $indexes = $this->getIndexes($tableName);

        foreach ($indexes as $name => $index) {
            if ($name === $indexName) {
                return new AlterInstructions([sprintf(
                    'DROP INDEX %s',
                    $this->quoteColumnName($indexName)
                )]);
            }
        }

        throw new InvalidArgumentException(sprintf(
            "The specified index name '%s' does not exist",
            $indexName
        ));
    }

    /**
     * @inheritDoc
     */
    public function hasPrimaryKey(string $tableName, $columns, ?string $constraint = null): bool
    {
        $primaryKey = $this->getPrimaryKey($tableName);

        if (empty($primaryKey['constraint'])) {
            return false;
        }

        if ($constraint) {
            return $primaryKey['constraint'] === $constraint;
        } else {
            if (is_string($columns)) {
                $columns = [$columns]; // str to array
            }
            $missingColumns = array_diff($columns, $primaryKey['columns']);

            return empty($missingColumns);
        }
    }

    /**
     * Get the primary key from a particular table.
     *
     * @param string $tableName Table name
     * @return array
     */
    public function getPrimaryKey(string $tableName): array
    {
        $options = $this->getOptions();
        $rows = $this->fetchAll(sprintf(
            "SELECT
                k.CONSTRAINT_NAME,
                k.COLUMN_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
                USING(CONSTRAINT_NAME,TABLE_SCHEMA,TABLE_NAME)
            WHERE t.CONSTRAINT_TYPE='PRIMARY KEY'
                AND t.TABLE_SCHEMA='%s'
                AND t.TABLE_NAME='%s'",
            $options['name'],
            $tableName
        ));

        $primaryKey = [
            'columns' => [],
        ];
        foreach ($rows as $row) {
            $primaryKey['constraint'] = $row['CONSTRAINT_NAME'];
            $primaryKey['columns'][] = $row['COLUMN_NAME'];
        }

        return $primaryKey;
    }

    /**
     * @inheritDoc
     */
    public function hasForeignKey(string $tableName, $columns, ?string $constraint = null): bool
    {
        if (is_string($columns)) {
            $columns = [$columns]; // str to array
        }
        $foreignKeys = $this->getForeignKeys($tableName);
        if ($constraint) {
            if (isset($foreignKeys[$constraint])) {
                return !empty($foreignKeys[$constraint]);
            }

            return false;
        }

        foreach ($foreignKeys as $key) {
            if ($columns == $key['columns']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get an array of foreign keys from a particular table.
     *
     * @param string $tableName Table name
     * @return array
     */
    protected function getForeignKeys(string $tableName): array
    {
        if (strpos($tableName, '.') !== false) {
            [$schema, $tableName] = explode('.', $tableName);
        }

        $foreignKeys = [];
        $rows = $this->fetchAll(sprintf(
            "SELECT
              CONSTRAINT_NAME,
              CONCAT(TABLE_SCHEMA, '.', TABLE_NAME) AS TABLE_NAME,
              COLUMN_NAME,
              CONCAT(REFERENCED_TABLE_SCHEMA, '.', REFERENCED_TABLE_NAME) AS REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME IS NOT NULL
              AND TABLE_SCHEMA = %s
              AND TABLE_NAME = '%s'
            ORDER BY POSITION_IN_UNIQUE_CONSTRAINT",
            empty($schema) ? 'DATABASE()' : "'$schema'",
            $tableName
        ));
        foreach ($rows as $row) {
            $foreignKeys[$row['CONSTRAINT_NAME']]['table'] = $row['TABLE_NAME'];
            $foreignKeys[$row['CONSTRAINT_NAME']]['columns'][] = $row['COLUMN_NAME'];
            $foreignKeys[$row['CONSTRAINT_NAME']]['referenced_table'] = $row['REFERENCED_TABLE_NAME'];
            $foreignKeys[$row['CONSTRAINT_NAME']]['referenced_columns'][] = $row['REFERENCED_COLUMN_NAME'];
        }

        return $foreignKeys;
    }

    /**
     * @inheritDoc
     */
    protected function getAddForeignKeyInstructions(Table $table, ForeignKey $foreignKey): AlterInstructions
    {
        $alter = sprintf(
            'ADD %s',
            $this->getForeignKeySqlDefinition($foreignKey)
        );

        return new AlterInstructions([$alter]);
    }

    /**
     * @inheritDoc
     */
    protected function getDropForeignKeyInstructions(string $tableName, string $constraint): AlterInstructions
    {
        $alter = sprintf(
            'DROP FOREIGN KEY %s',
            $constraint
        );

        return new AlterInstructions([$alter]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function getDropForeignKeyByColumnsInstructions(string $tableName, array $columns): AlterInstructions
    {
        $instructions = new AlterInstructions();

        foreach ($columns as $column) {
            $rows = $this->fetchAll(sprintf(
                "SELECT
                    CONSTRAINT_NAME
                  FROM information_schema.KEY_COLUMN_USAGE
                  WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                    AND TABLE_NAME = '%s'
                    AND COLUMN_NAME = '%s'
                  ORDER BY POSITION_IN_UNIQUE_CONSTRAINT",
                $tableName,
                $column
            ));

            foreach ($rows as $row) {
                $instructions->merge($this->getDropForeignKeyInstructions($tableName, $row['CONSTRAINT_NAME']));
            }
        }

        if (empty($instructions->getAlterParts())) {
            throw new InvalidArgumentException(sprintf(
                "Not foreign key on columns '%s' exist",
                implode(',', $columns)
            ));
        }

        return $instructions;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Phinx\Db\Adapter\UnsupportedColumnTypeException
     */
    public function getSqlType($type, ?int $limit = null): array
    {
        switch ($type) {
            case static::PHINX_TYPE_FLOAT:
            case static::PHINX_TYPE_DOUBLE:
            case static::PHINX_TYPE_DECIMAL:
            case static::PHINX_TYPE_DATE:
            case static::PHINX_TYPE_ENUM:
            case static::PHINX_TYPE_SET:
            case static::PHINX_TYPE_JSON:
            // Geospatial database types
            case static::PHINX_TYPE_GEOMETRY:
            case static::PHINX_TYPE_POINT:
            case static::PHINX_TYPE_LINESTRING:
            case static::PHINX_TYPE_POLYGON:
                return ['name' => $type];
            case static::PHINX_TYPE_DATETIME:
            case static::PHINX_TYPE_TIMESTAMP:
            case static::PHINX_TYPE_TIME:
                return ['name' => $type, 'limit' => $limit];
            case static::PHINX_TYPE_STRING:
                return ['name' => 'varchar', 'limit' => $limit ?: 255];
            case static::PHINX_TYPE_CHAR:
                return ['name' => 'char', 'limit' => $limit ?: 255];
            case static::PHINX_TYPE_TEXT:
                if ($limit) {
                    $sizes = [
                        // Order matters! Size must always be tested from longest to shortest!
                        'longtext' => static::TEXT_LONG,
                        'mediumtext' => static::TEXT_MEDIUM,
                        'text' => static::TEXT_REGULAR,
                        'tinytext' => static::TEXT_SMALL,
                    ];
                    foreach ($sizes as $name => $length) {
                        if ($limit >= $length) {
                            return ['name' => $name];
                        }
                    }
                }

                return ['name' => 'text'];
            case static::PHINX_TYPE_BINARY:
                if ($limit === null) {
                    $limit = 255;
                }

                if ($limit > 255) {
                    return $this->getSqlType(static::PHINX_TYPE_BLOB, $limit);
                }

                return ['name' => 'binary', 'limit' => $limit];
            case static::PHINX_TYPE_BINARYUUID:
                return ['name' => 'binary', 'limit' => 16];
            case static::PHINX_TYPE_VARBINARY:
                if ($limit === null) {
                    $limit = 255;
                }

                if ($limit > 255) {
                    return $this->getSqlType(static::PHINX_TYPE_BLOB, $limit);
                }

                return ['name' => 'varbinary', 'limit' => $limit];
            case static::PHINX_TYPE_BLOB:
                if ($limit !== null) {
                    // Rework this part as the choosen types were always UNDER the required length
                    $sizes = [
                        'tinyblob' => static::BLOB_SMALL,
                        'blob' => static::BLOB_REGULAR,
                        'mediumblob' => static::BLOB_MEDIUM,
                    ];

                    foreach ($sizes as $name => $length) {
                        if ($limit <= $length) {
                            return ['name' => $name];
                        }
                    }

                    // For more length requirement, the longblob is used
                    return ['name' => 'longblob'];
                }

                // If not limit is provided, fallback on blob
                return ['name' => 'blob'];
            case static::PHINX_TYPE_TINYBLOB:
                // Automatically reprocess blob type to ensure that correct blob subtype is selected given provided limit
                return $this->getSqlType(static::PHINX_TYPE_BLOB, $limit ?: static::BLOB_TINY);
            case static::PHINX_TYPE_MEDIUMBLOB:
                // Automatically reprocess blob type to ensure that correct blob subtype is selected given provided limit
                return $this->getSqlType(static::PHINX_TYPE_BLOB, $limit ?: static::BLOB_MEDIUM);
            case static::PHINX_TYPE_LONGBLOB:
                // Automatically reprocess blob type to ensure that correct blob subtype is selected given provided limit
                return $this->getSqlType(static::PHINX_TYPE_BLOB, $limit ?: static::BLOB_LONG);
            case static::PHINX_TYPE_BIT:
                return ['name' => 'bit', 'limit' => $limit ?: 64];
            case static::PHINX_TYPE_BIG_INTEGER:
                if ($limit === static::INT_BIG) {
                    $limit = static::INT_DISPLAY_BIG;
                }

                return ['name' => 'bigint', 'limit' => $limit ?: 20];
            case static::PHINX_TYPE_MEDIUM_INTEGER:
                if ($limit === static::INT_MEDIUM) {
                    $limit = static::INT_DISPLAY_MEDIUM;
                }

                return ['name' => 'mediumint', 'limit' => $limit ?: 8];
            case static::PHINX_TYPE_SMALL_INTEGER:
                if ($limit === static::INT_SMALL) {
                    $limit = static::INT_DISPLAY_SMALL;
                }

                return ['name' => 'smallint', 'limit' => $limit ?: 6];
            case static::PHINX_TYPE_TINY_INTEGER:
                if ($limit === static::INT_TINY) {
                    $limit = static::INT_DISPLAY_TINY;
                }

                return ['name' => 'tinyint', 'limit' => $limit ?: 4];
            case static::PHINX_TYPE_INTEGER:
                if ($limit && $limit >= static::INT_TINY) {
                    $sizes = [
                        // Order matters! Size must always be tested from longest to shortest!
                        'bigint' => static::INT_BIG,
                        'int' => static::INT_REGULAR,
                        'mediumint' => static::INT_MEDIUM,
                        'smallint' => static::INT_SMALL,
                        'tinyint' => static::INT_TINY,
                    ];
                    $limits = [
                        'tinyint' => static::INT_DISPLAY_TINY,
                        'smallint' => static::INT_DISPLAY_SMALL,
                        'mediumint' => static::INT_DISPLAY_MEDIUM,
                        'int' => static::INT_DISPLAY_REGULAR,
                        'bigint' => static::INT_DISPLAY_BIG,
                    ];
                    foreach ($sizes as $name => $length) {
                        if ($limit >= $length) {
                            $def = ['name' => $name];
                            if (isset($limits[$name])) {
                                $def['limit'] = $limits[$name];
                            }

                            return $def;
                        }
                    }
                } elseif (!$limit) {
                    $limit = static::INT_DISPLAY_REGULAR;
                }

                return ['name' => 'int', 'limit' => $limit];
            case static::PHINX_TYPE_BOOLEAN:
                return ['name' => 'tinyint', 'limit' => 1];
            case static::PHINX_TYPE_UUID:
                return ['name' => 'char', 'limit' => 36];
            case static::PHINX_TYPE_YEAR:
                if (!$limit || in_array($limit, [2, 4])) {
                    $limit = 4;
                }

                return ['name' => 'year', 'limit' => $limit];
            default:
                throw new UnsupportedColumnTypeException('Column type "' . $type . '" is not supported by MySQL.');
        }
    }

    /**
     * Returns Phinx type by SQL type
     *
     * @internal param string $sqlType SQL type
     * @param string $sqlTypeDef SQL Type definition
     * @throws \Phinx\Db\Adapter\UnsupportedColumnTypeException
     * @return array Phinx type
     */
    public function getPhinxType($sqlTypeDef)
    {
        $matches = [];
        if (!preg_match('/^([\w]+)(\(([\d]+)*(,([\d]+))*\))*(.+)*$/', $sqlTypeDef, $matches)) {
            throw new UnsupportedColumnTypeException('Column type "' . $sqlTypeDef . '" is not supported by MySQL.');
        }

        $limit = null;
        $scale = null;
        $type = $matches[1];
        if (count($matches) > 2) {
            $limit = $matches[3] ? (int)$matches[3] : null;
        }
        if (count($matches) > 4) {
            $scale = (int)$matches[5];
        }
        if ($type === 'tinyint' && $limit === 1) {
            $type = static::PHINX_TYPE_BOOLEAN;
            $limit = null;
        }
        switch ($type) {
            case 'varchar':
                $type = static::PHINX_TYPE_STRING;
                if ($limit === 255) {
                    $limit = null;
                }
                break;
            case 'char':
                $type = static::PHINX_TYPE_CHAR;
                if ($limit === 255) {
                    $limit = null;
                }
                if ($limit === 36) {
                    $type = static::PHINX_TYPE_UUID;
                }
                break;
            case 'tinyint':
                $type = static::PHINX_TYPE_TINY_INTEGER;
                break;
            case 'smallint':
                $type = static::PHINX_TYPE_SMALL_INTEGER;
                break;
            case 'mediumint':
                $type = static::PHINX_TYPE_MEDIUM_INTEGER;
                break;
            case 'int':
                $type = static::PHINX_TYPE_INTEGER;
                break;
            case 'bigint':
                $type = static::PHINX_TYPE_BIG_INTEGER;
                break;
            case 'bit':
                $type = static::PHINX_TYPE_BIT;
                if ($limit === 64) {
                    $limit = null;
                }
                break;
            case 'blob':
                $type = static::PHINX_TYPE_BLOB;
                $limit = static::BLOB_REGULAR;
                break;
            case 'tinyblob':
                $type = static::PHINX_TYPE_TINYBLOB;
                $limit = static::BLOB_TINY;
                break;
            case 'mediumblob':
                $type = static::PHINX_TYPE_MEDIUMBLOB;
                $limit = static::BLOB_MEDIUM;
                break;
            case 'longblob':
                $type = static::PHINX_TYPE_LONGBLOB;
                $limit = static::BLOB_LONG;
                break;
            case 'tinytext':
                $type = static::PHINX_TYPE_TEXT;
                $limit = static::TEXT_TINY;
                break;
            case 'mediumtext':
                $type = static::PHINX_TYPE_TEXT;
                $limit = static::TEXT_MEDIUM;
                break;
            case 'longtext':
                $type = static::PHINX_TYPE_TEXT;
                $limit = static::TEXT_LONG;
                break;
            case 'binary':
                if ($limit === null) {
                    $limit = 255;
                }

                if ($limit > 255) {
                    $type = static::PHINX_TYPE_BLOB;
                    break;
                }

                if ($limit === 16) {
                    $type = static::PHINX_TYPE_BINARYUUID;
                }
                break;
        }

        try {
            // Call this to check if parsed type is supported.
            $this->getSqlType($type, $limit);
        } catch (UnsupportedColumnTypeException $e) {
            $type = Literal::from($type);
        }

        $phinxType = [
            'name' => $type,
            'limit' => $limit,
            'scale' => $scale,
        ];

        if ($type === static::PHINX_TYPE_ENUM || $type === static::PHINX_TYPE_SET) {
            $values = trim($matches[6], '()');
            $phinxType['values'] = [];
            $opened = false;
            $escaped = false;
            $wasEscaped = false;
            $value = '';
            $valuesLength = strlen($values);
            for ($i = 0; $i < $valuesLength; $i++) {
                $char = $values[$i];
                if ($char === "'" && !$opened) {
                    $opened = true;
                } elseif (
                    !$escaped
                    && ($i + 1) < $valuesLength
                    && (
                        $char === "'" && $values[$i + 1] === "'"
                        || $char === '\\' && $values[$i + 1] === '\\'
                    )
                ) {
                    $escaped = true;
                } elseif ($char === "'" && $opened && !$escaped) {
                    $phinxType['values'][] = $value;
                    $value = '';
                    $opened = false;
                } elseif (($char === "'" || $char === '\\') && $opened && $escaped) {
                    $value .= $char;
                    $escaped = false;
                    $wasEscaped = true;
                } elseif ($opened) {
                    if ($values[$i - 1] === '\\' && !$wasEscaped) {
                        if ($char === 'n') {
                            $char = "\n";
                        } elseif ($char === 'r') {
                            $char = "\r";
                        } elseif ($char === 't') {
                            $char = "\t";
                        }
                        if ($values[$i] !== $char) {
                            $value = substr($value, 0, strlen($value) - 1);
                        }
                    }
                    $value .= $char;
                    $wasEscaped = false;
                }
            }
        }

        return $phinxType;
    }

    /**
     * @inheritDoc
     */
    public function createDatabase(string $name, array $options = []): void
    {
        $charset = $options['charset'] ?? 'utf8';

        if (isset($options['collation'])) {
            $this->execute(sprintf(
                'CREATE DATABASE `%s` DEFAULT CHARACTER SET `%s` COLLATE `%s`',
                $name,
                $charset,
                $options['collation']
            ));
        } else {
            $this->execute(sprintf('CREATE DATABASE `%s` DEFAULT CHARACTER SET `%s`', $name, $charset));
        }
    }

    /**
     * @inheritDoc
     */
    public function hasDatabase(string $name): bool
    {
        $rows = $this->fetchAll(
            sprintf(
                'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'%s\'',
                $name
            )
        );

        foreach ($rows as $row) {
            if (!empty($row)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function dropDatabase(string $name): void
    {
        $this->execute(sprintf('DROP DATABASE IF EXISTS `%s`', $name));
        $this->createdTables = [];
    }

    /**
     * Gets the MySQL Column Definition for a Column object.
     *
     * @param \Phinx\Db\Table\Column $column Column
     * @return string
     */
    protected function getColumnSqlDefinition(Column $column): string
    {
        if ($column->getType() instanceof Literal) {
            $def = (string)$column->getType();
        } else {
            $sqlType = $this->getSqlType($column->getType(), $column->getLimit());
            $def = strtoupper($sqlType['name']);
        }
        if ($column->getPrecision() && $column->getScale()) {
            $def .= '(' . $column->getPrecision() . ',' . $column->getScale() . ')';
        } elseif (isset($sqlType['limit'])) {
            $def .= '(' . $sqlType['limit'] . ')';
        }

        $values = $column->getValues();
        if ($values && is_array($values)) {
            $def .= '(' . implode(', ', array_map(function ($value) {
                // we special case NULL as it's not actually allowed an enum value,
                // and we want MySQL to issue an error on the create statement, but
                // quote coerces it to an empty string, which will not error
                return $value === null ? 'NULL' : $this->getConnection()->quote($value);
            }, $values)) . ')';
        }

        $def .= $column->getEncoding() ? ' CHARACTER SET ' . $column->getEncoding() : '';
        $def .= $column->getCollation() ? ' COLLATE ' . $column->getCollation() : '';
        $def .= !$column->isSigned() && isset($this->signedColumnTypes[$column->getType()]) ? ' unsigned' : '';
        $def .= $column->isNull() ? ' NULL' : ' NOT NULL';

        if (
            version_compare($this->getAttribute(\PDO::ATTR_SERVER_VERSION), '8', '>=')
            && in_array($column->getType(), static::PHINX_TYPES_GEOSPATIAL)
            && !is_null($column->getSrid())
        ) {
            $def .= " SRID {$column->getSrid()}";
        }

        $def .= $column->isIdentity() ? ' AUTO_INCREMENT' : '';

        $default = $column->getDefault();
        // MySQL 8 supports setting default for the following tested types, but only if they are "cast as expressions"
        if (
            version_compare($this->getAttribute(\PDO::ATTR_SERVER_VERSION), '8', '>=') &&
            is_string($default) &&
            in_array(
                $column->getType(),
                array_merge(
                    static::PHINX_TYPES_GEOSPATIAL,
                    [static::PHINX_TYPE_BLOB, static::PHINX_TYPE_JSON, static::PHINX_TYPE_TEXT]
                )
            )
        ) {
            $default = Literal::from('(' . $this->getConnection()->quote($column->getDefault()) . ')');
        }
        $def .= $this->getDefaultValueDefinition($default, $column->getType());

        if ($column->getComment()) {
            $def .= ' COMMENT ' . $this->getConnection()->quote($column->getComment());
        }

        if ($column->getUpdate()) {
            $def .= ' ON UPDATE ' . $column->getUpdate();
        }

        return $def;
    }

    /**
     * Gets the MySQL Index Definition for an Index object.
     *
     * @param \Phinx\Db\Table\Index $index Index
     * @return string
     */
    protected function getIndexSqlDefinition(Index $index): string
    {
        $def = '';
        $limit = '';

        if ($index->getType() === Index::UNIQUE) {
            $def .= ' UNIQUE';
        }

        if ($index->getType() === Index::FULLTEXT) {
            $def .= ' FULLTEXT';
        }

        $def .= ' KEY';

        if (is_string($index->getName())) {
            $def .= ' `' . $index->getName() . '`';
        }

        $columnNames = $index->getColumns();
        $order = $index->getOrder() ?? [];
        $columnNames = array_map(function ($columnName) use ($order) {
            $ret = '`' . $columnName . '`';
            if (isset($order[$columnName])) {
                $ret .= ' ' . $order[$columnName];
            }

            return $ret;
        }, $columnNames);

        if (!is_array($index->getLimit())) {
            if ($index->getLimit()) {
                $limit = '(' . $index->getLimit() . ')';
            }
            $def .= ' (' . implode(',', $columnNames) . $limit . ')';
        } else {
            $columns = $index->getColumns();
            $limits = $index->getLimit();
            $def .= ' (';
            foreach ($columns as $column) {
                $limit = !isset($limits[$column]) || $limits[$column] <= 0 ? '' : '(' . $limits[$column] . ')';
                $columnSort = isset($order[$column]) ?? '';
                $def .= '`' . $column . '`' . $limit . ' ' . $columnSort . ', ';
            }
            $def = rtrim($def, ', ');
            $def .= ' )';
        }

        return $def;
    }

    /**
     * Gets the MySQL Foreign Key Definition for an ForeignKey object.
     *
     * @param \Phinx\Db\Table\ForeignKey $foreignKey Foreign key
     * @return string
     */
    protected function getForeignKeySqlDefinition(ForeignKey $foreignKey): string
    {
        $def = '';
        if ($foreignKey->getConstraint()) {
            $def .= ' CONSTRAINT ' . $this->quoteColumnName($foreignKey->getConstraint());
        }
        $columnNames = [];
        foreach ($foreignKey->getColumns() as $column) {
            $columnNames[] = $this->quoteColumnName($column);
        }
        $def .= ' FOREIGN KEY (' . implode(',', $columnNames) . ')';
        $refColumnNames = [];
        foreach ($foreignKey->getReferencedColumns() as $column) {
            $refColumnNames[] = $this->quoteColumnName($column);
        }
        $def .= ' REFERENCES ' . $this->quoteTableName($foreignKey->getReferencedTable()->getName()) . ' (' . implode(',', $refColumnNames) . ')';
        if ($foreignKey->getOnDelete()) {
            $def .= ' ON DELETE ' . $foreignKey->getOnDelete();
        }
        if ($foreignKey->getOnUpdate()) {
            $def .= ' ON UPDATE ' . $foreignKey->getOnUpdate();
        }

        return $def;
    }

    /**
     * Describes a database table. This is a MySQL adapter specific method.
     *
     * @param string $tableName Table name
     * @return array
     */
    public function describeTable(string $tableName): array
    {
        $options = $this->getOptions();

        // mysql specific
        $sql = sprintf(
            "SELECT *
             FROM information_schema.tables
             WHERE table_schema = '%s'
             AND table_name = '%s'",
            $options['name'],
            $tableName
        );

        $table = $this->fetchRow($sql);

        return $table !== false ? $table : [];
    }

    /**
     * Returns MySQL column types (inherited and MySQL specified).
     *
     * @return string[]
     */
    public function getColumnTypes(): array
    {
        return array_merge(parent::getColumnTypes(), static::$specificColumnTypes);
    }

    /**
     * @inheritDoc
     */
    public function getDecoratedConnection(): Connection
    {
        $options = $this->getOptions();
        $options = [
            'username' => $options['user'] ?? null,
            'password' => $options['pass'] ?? null,
            'database' => $options['name'],
            'quoteIdentifiers' => true,
        ] + $options;

        $driver = new MysqlDriver($options);
        $driver->setConnection($this->connection);

        return new Connection(['driver' => $driver] + $options);
    }
}
