<?php
/**
 * Phinx
 *
 * (The MIT license)
 * Copyright (c) 2015 Rob Morgan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated * documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package    Phinx
 * @subpackage Phinx\Db\Adapter
 */
namespace Phinx\Db\Adapter;

use Phinx\Db\Table;
use Phinx\Db\Table\Column;
use Phinx\Db\Table\Index;
use Phinx\Db\Table\ForeignKey;

class PostgresAdapter extends PdoAdapter implements AdapterInterface
{
    const INT_SMALL = 65535;

    /**
     * Columns with comments
     *
     * @var array
     */
    protected $columnsWithComments = array();

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if (null === $this->connection) {
            if (!class_exists('PDO') || !in_array('pgsql', \PDO::getAvailableDrivers(), true)) {
                // @codeCoverageIgnoreStart
                throw new \RuntimeException('You need to enable the PDO_Pgsql extension for Phinx to run properly.');
                // @codeCoverageIgnoreEnd
            }

            $db = null;
            $options = $this->getOptions();

            // if port is specified use it, otherwise use the PostgreSQL default
            if (isset($options['port'])) {
                $dsn = 'pgsql:host=' . $options['host'] . ';port=' . $options['port'] . ';dbname=' . $options['name'];
            } else {
                $dsn = 'pgsql:host=' . $options['host'] . ';dbname=' . $options['name'];
            }

            try {
                $db = new \PDO($dsn, $options['user'], $options['pass'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
            } catch (\PDOException $exception) {
                throw new \InvalidArgumentException(sprintf(
                    'There was a problem connecting to the database: %s',
                    $exception->getMessage()
                ));
            }

            $this->setConnection($db);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransactions()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->execute('BEGIN');
    }

    /**
     * {@inheritdoc}
     */
    public function commitTransaction()
    {
        $this->execute('COMMIT');
    }

    /**
     * {@inheritdoc}
     */
    public function rollbackTransaction()
    {
        $this->execute('ROLLBACK');
    }

    /**
     * Quotes a schema name for use in a query.
     *
     * @param string $schemaName Schema Name
     * @return string
     */
    public function quoteSchemaName($schemaName)
    {
        return $this->quoteColumnName($schemaName);
    }

    /**
     * {@inheritdoc}
     */
    public function quoteTableName($tableName)
    {
        return $this->quoteSchemaName($this->getSchemaName()) . '.' . $this->quoteColumnName($tableName);
    }

    /**
     * {@inheritdoc}
     */
    public function quoteColumnName($columnName)
    {
        return '"'. $columnName . '"';
    }

    /**
     * {@inheritdoc}
     */
    public function hasTable($tableName)
    {
        $result = $this->getConnection()->query(
            sprintf(
                'SELECT *
                FROM information_schema.tables
                WHERE table_schema = %s
                AND lower(table_name) = lower(%s)',
                $this->getConnection()->quote($this->getSchemaName()),
                $this->getConnection()->quote($tableName)
            )
        );

        return $result->rowCount() === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function createTable(Table $table)
    {
        $this->startCommandTimer();
        $options = $table->getOptions();

         // Add the default primary key
        $columns = $table->getPendingColumns();
        if (!isset($options['id']) || (isset($options['id']) && $options['id'] === true)) {
            $column = new Column();
            $column->setName('id')
                   ->setType('integer')
                   ->setIdentity(true);

            array_unshift($columns, $column);
            $options['primary_key'] = 'id';

        } elseif (isset($options['id']) && is_string($options['id'])) {
            // Handle id => "field_name" to support AUTO_INCREMENT
            $column = new Column();
            $column->setName($options['id'])
                   ->setType('integer')
                   ->setIdentity(true);

            array_unshift($columns, $column);
            $options['primary_key'] = $options['id'];
        }

        // TODO - process table options like collation etc
        $sql = 'CREATE TABLE ';
        $sql .= $this->quoteTableName($table->getName()) . ' (';

        $this->columnsWithComments = array();
        foreach ($columns as $column) {
            $sql .= $this->quoteColumnName($column->getName()) . ' ' . $this->getColumnSqlDefinition($column) . ', ';

            // set column comments, if needed
            if ($column->getComment()) {
                $this->columnsWithComments[] = $column;
            }
        }

         // set the primary key(s)
        if (isset($options['primary_key'])) {
            $sql = rtrim($sql);
            $sql .= sprintf(' CONSTRAINT %s_pkey PRIMARY KEY (', $table->getName());
            if (is_string($options['primary_key'])) {       // handle primary_key => 'id'
                $sql .= $this->quoteColumnName($options['primary_key']);
            } elseif (is_array($options['primary_key'])) { // handle primary_key => array('tag_id', 'resource_id')
                // PHP 5.4 will allow access of $this, so we can call quoteColumnName() directly in the anonymous function,
                // but for now just hard-code the adapter quotes
                $sql .= implode(
                    ',',
                    array_map(
                        function ($v) {
                            return '"' . $v . '"';
                        },
                        $options['primary_key']
                    )
                );
            }
            $sql .= ')';
        } else {
            $sql = substr(rtrim($sql), 0, -1);              // no primary keys
        }

        // set the foreign keys
        $foreignKeys = $table->getForeignKeys();
        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $foreignKey) {
                $sql .= ', ' . $this->getForeignKeySqlDefinition($foreignKey, $table->getName());
            }
        }

        $sql .= ');';

        // process column comments
        if (!empty($this->columnsWithComments)) {
            foreach ($this->columnsWithComments as $column) {
                $sql .= $this->getColumnCommentSqlDefinition($column, $table->getName());
            }
        }

        // set the indexes
        $indexes = $table->getIndexes();
        if (!empty($indexes)) {
            foreach ($indexes as $index) {
                $sql .= $this->getIndexSqlDefinition($index, $table->getName());
            }
        }

        // execute the sql
        $this->writeCommand('createTable', array($table->getName()));
        $this->execute($sql);

        // process table comments
        if (isset($options['comment'])) {
            $sql = sprintf(
                'COMMENT ON TABLE %s IS %s',
                $this->quoteTableName($table->getName()),
                $this->getConnection()->quote($options['comment'])
            );
            $this->execute($sql);
        }

        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function renameTable($tableName, $newTableName)
    {
        $this->startCommandTimer();
        $this->writeCommand('renameTable', array($tableName, $newTableName));
        $sql = sprintf(
            'ALTER TABLE %s RENAME TO %s',
            $this->quoteTableName($tableName),
            $this->quoteColumnName($newTableName)
        );
        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function dropTable($tableName)
    {
        $this->startCommandTimer();
        $this->writeCommand('dropTable', array($tableName));
        $this->execute(sprintf('DROP TABLE %s', $this->quoteTableName($tableName)));
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($tableName)
    {
        $columns = array();
        $sql = sprintf(
            "SELECT column_name, data_type, is_identity, is_nullable,
             column_default, character_maximum_length, numeric_precision, numeric_scale
             FROM information_schema.columns
             WHERE table_name ='%s'",
            $tableName
        );
        $columnsInfo = $this->fetchAll($sql);

        foreach ($columnsInfo as $columnInfo) {
            $column = new Column();
            $column->setName($columnInfo['column_name'])
                   ->setType($this->getPhinxType($columnInfo['data_type']))
                   ->setNull($columnInfo['is_nullable'] === 'YES')
                   ->setDefault($columnInfo['column_default'])
                   ->setIdentity($columnInfo['is_identity'] === 'YES')
                   ->setPrecision($columnInfo['numeric_precision'])
                   ->setScale($columnInfo['numeric_scale']);

            if (preg_match('/\bwith time zone$/', $columnInfo['data_type'])) {
                $column->setTimezone(true);
            }

            if (isset($columnInfo['character_maximum_length'])) {
                $column->setLimit($columnInfo['character_maximum_length']);
            }
            $columns[] = $column;
        }
        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function hasColumn($tableName, $columnName, $options = array())
    {
        $sql = sprintf("SELECT count(*)
            FROM information_schema.columns
            WHERE table_schema = '%s' AND table_name = '%s' AND column_name = '%s'",
            $this->getSchemaName(),
            $tableName,
            $columnName
        );

        $result = $this->fetchRow($sql);
        return  $result['count'] > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn(Table $table, Column $column)
    {
        $this->startCommandTimer();
        $this->writeCommand('addColumn', array($table->getName(), $column->getName(), $column->getType()));
        $sql = sprintf(
            'ALTER TABLE %s ADD %s %s',
            $this->quoteTableName($table->getName()),
            $this->quoteColumnName($column->getName()),
            $this->getColumnSqlDefinition($column)
        );

        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function renameColumn($tableName, $columnName, $newColumnName)
    {
        $this->startCommandTimer();
        $sql = sprintf(
            "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END AS column_exists
             FROM information_schema.columns
             WHERE table_name ='%s' AND column_name = '%s'",
            $tableName,
            $columnName
        );
        $result = $this->fetchRow($sql);
        if (!(bool) $result['column_exists']) {
            throw new \InvalidArgumentException("The specified column does not exist: $columnName");
        }
        $this->writeCommand('renameColumn', array($tableName, $columnName, $newColumnName));
        $this->execute(
            sprintf(
                'ALTER TABLE %s RENAME COLUMN %s TO %s',
                $this->quoteTableName($tableName),
                $this->quoteColumnName($columnName),
                $newColumnName
            )
        );
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function changeColumn($tableName, $columnName, Column $newColumn)
    {
        // TODO - is it possible to merge these 3 queries into less?
        $this->startCommandTimer();
        $this->writeCommand('changeColumn', array($tableName, $columnName, $newColumn->getType()));
        // change data type
        $sql = sprintf(
            'ALTER TABLE %s ALTER COLUMN %s TYPE %s',
            $this->quoteTableName($tableName),
            $this->quoteColumnName($columnName),
            $this->getColumnSqlDefinition($newColumn)
        );
        //NULL and DEFAULT cannot be set while changing column type
        $sql = preg_replace('/ NOT NULL/', '', $sql);
        $sql = preg_replace('/ NULL/', '', $sql);
        //If it is set, DEFAULT is the last definition
        $sql = preg_replace('/DEFAULT .*/', '', $sql);
        $this->execute($sql);
        // process null
        $sql = sprintf(
            'ALTER TABLE %s ALTER COLUMN %s',
            $this->quoteTableName($tableName),
            $this->quoteColumnName($columnName)
        );
        if ($newColumn->isNull()) {
            $sql .= ' DROP NOT NULL';
        } else {
            $sql .= ' SET NOT NULL';
        }
        $this->execute($sql);
        if (!is_null($newColumn->getDefault())) {
            //change default
            $this->execute(
                sprintf(
                    'ALTER TABLE %s ALTER COLUMN %s SET %s',
                    $this->quoteTableName($tableName),
                    $this->quoteColumnName($columnName),
                    $this->getDefaultValueDefinition($newColumn->getDefault())
                )
            );
        }
        else {
            //drop default
            $this->execute(
                sprintf(
                    'ALTER TABLE %s ALTER COLUMN %s DROP DEFAULT',
                    $this->quoteTableName($tableName),
                    $this->quoteColumnName($columnName)
                )
            );
        }
        // rename column
        if ($columnName !== $newColumn->getName()) {
            $this->execute(
                sprintf(
                    'ALTER TABLE %s RENAME COLUMN %s TO %s',
                    $this->quoteTableName($tableName),
                    $this->quoteColumnName($columnName),
                    $this->quoteColumnName($newColumn->getName())
                )
            );
        }

        // change column comment if needed
        if ($newColumn->getComment()) {
            $sql = $this->getColumnCommentSqlDefinition($newColumn, $tableName);
            $this->execute($sql);
        }

        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function dropColumn($tableName, $columnName)
    {
        $this->startCommandTimer();
        $this->writeCommand('dropColumn', array($tableName, $columnName));
        $this->execute(
            sprintf(
                'ALTER TABLE %s DROP COLUMN %s',
                $this->quoteTableName($tableName),
                $this->quoteColumnName($columnName)
            )
        );
        $this->endCommandTimer();
    }

    /**
     * Get an array of indexes from a particular table.
     *
     * @param string $tableName Table Name
     * @return array
     */
    protected function getIndexes($tableName)
    {
        $indexes = array();
        $sql = "SELECT
            i.relname AS index_name,
            a.attname AS column_name
        FROM
            pg_class t,
            pg_class i,
            pg_index ix,
            pg_attribute a
        WHERE
            t.oid = ix.indrelid
            AND i.oid = ix.indexrelid
            AND a.attrelid = t.oid
            AND a.attnum = ANY(ix.indkey)
            AND t.relkind = 'r'
            AND t.relname = '$tableName'
        ORDER BY
            t.relname,
            i.relname;";
        $rows = $this->fetchAll($sql);
        foreach ($rows as $row) {
            if (!isset($indexes[$row['index_name']])) {
                $indexes[$row['index_name']] = array('columns' => array());
            }
            $indexes[$row['index_name']]['columns'][] = strtolower($row['column_name']);
        }
        return $indexes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIndex($tableName, $columns)
    {
        if (is_string($columns)) {
            $columns = array($columns);
        }
        $columns = array_map('strtolower', $columns);
        $indexes = $this->getIndexes($tableName);
        foreach ($indexes as $index) {
            if (array_diff($index['columns'], $columns) === array_diff($columns, $index['columns'])) {
                return true;
            }
        }
        return false;
    }

     /**
      * {@inheritdoc}
      */
     public function hasIndexByName($tableName, $indexName)
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
     * {@inheritdoc}
     */
    public function addIndex(Table $table, Index $index)
    {
        $this->startCommandTimer();
        $this->writeCommand('addIndex', array($table->getName(), $index->getColumns()));
        $sql = $this->getIndexSqlDefinition($index, $table->getName());
        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function dropIndex($tableName, $columns)
    {
        $this->startCommandTimer();
        if (is_string($columns)) {
            $columns = array($columns); // str to array
        }

        $this->writeCommand('dropIndex', array($tableName, $columns));
        $indexes = $this->getIndexes($tableName);
        $columns = array_map('strtolower', $columns);

        foreach ($indexes as $indexName => $index) {
            $a = array_diff($columns, $index['columns']);
            if (empty($a)) {
                $this->execute(
                    sprintf(
                        'DROP INDEX IF EXISTS %s',
                        $this->quoteColumnName($indexName)
                    )
                );
                $this->endCommandTimer();
                return;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dropIndexByName($tableName, $indexName)
    {
        $this->startCommandTimer();
        $this->writeCommand('dropIndexByName', array($tableName, $indexName));
        $sql = sprintf(
            'DROP INDEX IF EXISTS %s',
            $indexName
        );
        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function hasForeignKey($tableName, $columns, $constraint = null)
    {
        if (is_string($columns)) {
            $columns = array($columns); // str to array
        }
        $foreignKeys = $this->getForeignKeys($tableName);
        if ($constraint) {
            if (isset($foreignKeys[$constraint])) {
                return !empty($foreignKeys[$constraint]);
            }
            return false;
        } else {
            foreach ($foreignKeys as $key) {
                $a = array_diff($columns, $key['columns']);
                if (empty($a)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Get an array of foreign keys from a particular table.
     *
     * @param string $tableName Table Name
     * @return array
     */
    protected function getForeignKeys($tableName)
    {
        $foreignKeys = array();
        $rows = $this->fetchAll(sprintf(
            "SELECT
                    tc.constraint_name,
                    tc.table_name, kcu.column_name,
                    ccu.table_name AS referenced_table_name,
                    ccu.column_name AS referenced_column_name
                FROM
                    information_schema.table_constraints AS tc
                    JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
                    JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
                WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name = '%s'
                ORDER BY kcu.position_in_unique_constraint",
            $tableName
        ));
        foreach ($rows as $row) {
            $foreignKeys[$row['constraint_name']]['table'] = $row['table_name'];
            $foreignKeys[$row['constraint_name']]['columns'][] = $row['column_name'];
            $foreignKeys[$row['constraint_name']]['referenced_table'] = $row['referenced_table_name'];
            $foreignKeys[$row['constraint_name']]['referenced_columns'][] = $row['referenced_column_name'];
        }
        return $foreignKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function addForeignKey(Table $table, ForeignKey $foreignKey)
    {
        $this->startCommandTimer();
        $this->writeCommand('addForeignKey', array($table->getName(), $foreignKey->getColumns()));
        $sql = sprintf(
            'ALTER TABLE %s ADD %s',
            $this->quoteTableName($table->getName()),
            $this->getForeignKeySqlDefinition($foreignKey, $table->getName())
        );
        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function dropForeignKey($tableName, $columns, $constraint = null)
    {
        $this->startCommandTimer();
        if (is_string($columns)) {
            $columns = array($columns); // str to array
        }
        $this->writeCommand('dropForeignKey', array($tableName, $columns));

        if ($constraint) {
            $this->execute(
                sprintf(
                    'ALTER TABLE %s DROP CONSTRAINT %s',
                    $this->quoteTableName($tableName),
                    $constraint
                )
            );
        } else {
            foreach ($columns as $column) {
                $rows = $this->fetchAll(sprintf(
                    "SELECT CONSTRAINT_NAME
                      FROM information_schema.KEY_COLUMN_USAGE
                      WHERE TABLE_SCHEMA = CURRENT_SCHEMA()
                        AND TABLE_NAME IS NOT NULL
                        AND TABLE_NAME = '%s'
                        AND COLUMN_NAME = '%s'
                      ORDER BY POSITION_IN_UNIQUE_CONSTRAINT",
                    $tableName,
                    $column
                ));

                foreach ($rows as $row) {
                    $this->dropForeignKey($tableName, $columns, $row['constraint_name']);
                }
            }
        }
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlType($type, $limit = null)
    {
        switch ($type) {
            case static::PHINX_TYPE_INTEGER:
                if ($limit && $limit == static::INT_SMALL) {
                    return array(
                        'name' => 'smallint',
                        'limit' => static::INT_SMALL
                    );
                }
                return array('name' => $type);
            case static::PHINX_TYPE_TEXT:
            case static::PHINX_TYPE_TIME:
            case static::PHINX_TYPE_DATE:
            case static::PHINX_TYPE_BOOLEAN:
            case static::PHINX_TYPE_JSON:
            case static::PHINX_TYPE_JSONB:
            case static::PHINX_TYPE_UUID:
                return array('name' => $type);
            case static::PHINX_TYPE_DECIMAL:
                return array('name' => $type, 'precision' => 18, 'scale' => 0);
            case static::PHINX_TYPE_STRING:
                return array('name' => 'character varying', 'limit' => 255);
            case static::PHINX_TYPE_CHAR:
                return array('name' => 'character', 'limit' => 255);
            case static::PHINX_TYPE_BIG_INTEGER:
                return array('name' => 'bigint');
            case static::PHINX_TYPE_FLOAT:
                return array('name' => 'real');
            case static::PHINX_TYPE_DATETIME:
            case static::PHINX_TYPE_TIMESTAMP:
                return array('name' => 'timestamp');
            case static::PHINX_TYPE_BLOB:
            case static::PHINX_TYPE_BINARY:
                return array('name' => 'bytea');
            // Geospatial database types
            // Spatial storage in Postgres is done via the PostGIS extension,
            // which enables the use of the "geography" type in combination
            // with SRID 4326.
            case static::PHINX_TYPE_GEOMETRY:
                return array('name' => 'geography', 'geometry', 4326);
                break;
            case static::PHINX_TYPE_POINT:
                return array('name' => 'geography', 'point', 4326);
                break;
            case static::PHINX_TYPE_LINESTRING:
                return array('name' => 'geography', 'linestring', 4326);
                break;
            case static::PHINX_TYPE_POLYGON:
                return array('name' => 'geography', 'polygon', 4326);
                break;
            default:
                if ($this->isArrayType($type)) {
                    return array('name' => $type);
                }
                // Return array type
                throw new \RuntimeException('The type: "' . $type . '" is not supported');
        }
    }

    /**
     * Returns Phinx type by SQL type
     *
     * @param string $sqlType SQL type
     * @returns string Phinx type
     */
    public function getPhinxType($sqlType)
    {
        switch ($sqlType) {
            case 'character varying':
            case 'varchar':
                return static::PHINX_TYPE_STRING;
            case 'character':
            case 'char':
                return static::PHINX_TYPE_CHAR;
            case 'text':
                return static::PHINX_TYPE_TEXT;
            case 'json':
                return static::PHINX_TYPE_JSON;
            case 'jsonb':
                return static::PHINX_TYPE_JSONB;
            case 'smallint':
                return array(
                    'name' => 'smallint',
                    'limit' => static::INT_SMALL
                );
            case 'int':
            case 'int4':
            case 'integer':
                return static::PHINX_TYPE_INTEGER;
            case 'decimal':
            case 'numeric':
                return static::PHINX_TYPE_DECIMAL;
            case 'bigint':
            case 'int8':
                return static::PHINX_TYPE_BIG_INTEGER;
            case 'real':
            case 'float4':
                return static::PHINX_TYPE_FLOAT;
            case 'bytea':
                return static::PHINX_TYPE_BINARY;
                break;
            case 'time':
            case 'timetz':
            case 'time with time zone':
            case 'time without time zone':
                return static::PHINX_TYPE_TIME;
            case 'date':
                return static::PHINX_TYPE_DATE;
            case 'timestamp':
            case 'timestamptz':
            case 'timestamp with time zone':
            case 'timestamp without time zone':
                return static::PHINX_TYPE_DATETIME;
            case 'bool':
            case 'boolean':
                return static::PHINX_TYPE_BOOLEAN;
            case 'uuid':
                return static::PHINX_TYPE_UUID;
            default:
                throw new \RuntimeException('The PostgreSQL type: "' . $sqlType . '" is not supported');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createDatabase($name, $options = array())
    {
        $this->startCommandTimer();
        $this->writeCommand('createDatabase', array($name));
        $charset = isset($options['charset']) ? $options['charset'] : 'utf8';
        $this->execute(sprintf("CREATE DATABASE %s WITH ENCODING = '%s'", $name, $charset));
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function hasDatabase($databaseName)
    {
        $sql = sprintf("SELECT count(*) FROM pg_database WHERE datname = '%s'", $databaseName);
        $result = $this->fetchRow($sql);
        return  $result['count'] > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function dropDatabase($name)
    {
        $this->startCommandTimer();
        $this->writeCommand('dropDatabase', array($name));
        $this->disconnect();
        $this->execute(sprintf('DROP DATABASE IF EXISTS %s', $name));
        $this->connect();
        $this->endCommandTimer();
    }

    /**
     * Get the defintion for a `DEFAULT` statement.
     *
     * @param  mixed $default
     * @return string
     */
    protected function getDefaultValueDefinition($default)
    {
        if (is_string($default) && 'CURRENT_TIMESTAMP' !== $default) {
            $default = $this->getConnection()->quote($default);
        } elseif (is_bool($default)) {
            $default = $this->castToBool($default);
        }
        return isset($default) ? 'DEFAULT ' . $default : '';
    }

    /**
     * Gets the PostgreSQL Column Definition for a Column object.
     *
     * @param Column $column Column
     * @return string
     */
    protected function getColumnSqlDefinition(Column $column)
    {
        $buffer = array();
        if ($column->isIdentity()) {
            $buffer[] = $column->getType() == 'biginteger' ? 'BIGSERIAL' : 'SERIAL';
        } else {
            $sqlType = $this->getSqlType($column->getType(), $column->getLimit());
            $buffer[] = strtoupper($sqlType['name']);
            // integers cant have limits in postgres
            if (static::PHINX_TYPE_DECIMAL === $sqlType['name'] && ($column->getPrecision() || $column->getScale())) {
                $buffer[] = sprintf(
                    '(%s, %s)',
                    $column->getPrecision() ? $column->getPrecision() : $sqlType['precision'],
                    $column->getScale() ? $column->getScale() : $sqlType['scale']
                );
            } elseif (!in_array($sqlType['name'], array('integer', 'smallint'))) {
                if ($column->getLimit() || isset($sqlType['limit'])) {
                    $buffer[] = sprintf('(%s)', $column->getLimit() ? $column->getLimit() : $sqlType['limit']);
                }
            }

            $timeTypes = array(
                'time',
                'timestamp',
            );
            if (in_array($sqlType['name'], $timeTypes) && $column->isTimezone()) {
                $buffer[] = strtoupper('with time zone');
            }
        }

        $buffer[] = $column->isNull() ? 'NULL' : 'NOT NULL';

        if (!is_null($column->getDefault())) {
            $buffer[] = $this->getDefaultValueDefinition($column->getDefault());
        }

        return implode(' ', $buffer);
    }

    /**
     * Gets the PostgreSQL Column Comment Defininition for a column object.
     *
     * @param Column $column Column
     * @param string $tableName Table name
     * @return string
     */
    protected function getColumnCommentSqlDefinition(Column $column, $tableName)
    {
        // passing 'null' is to remove column comment
        $comment = (strcasecmp($column->getComment(), 'NULL') !== 0)
                 ? $this->getConnection()->quote($column->getComment())
                 : 'NULL';

        return sprintf(
            'COMMENT ON COLUMN %s.%s IS %s;',
            $tableName,
            $column->getName(),
            $comment
        );
    }

    /**
     * Gets the PostgreSQL Index Definition for an Index object.
     *
     * @param Index  $index Index
     * @param string $tableName Table name
     * @return string
     */
    protected function getIndexSqlDefinition(Index $index, $tableName)
    {
        if (is_string($index->getName())) {
            $indexName = $index->getName();
        } else {
            $columnNames = $index->getColumns();
            if (is_string($columnNames)) {
                $columnNames = array($columnNames);
            }
            $indexName = sprintf('%s_%s', $tableName, implode('_', $columnNames));
        }
        $def = sprintf(
            "CREATE %s INDEX %s ON %s (%s);",
            ($index->getType() === Index::UNIQUE ? 'UNIQUE' : ''),
            $indexName,
            $this->quoteTableName($tableName),
            implode(',', $index->getColumns())
        );
        return $def;
    }

    /**
     * Gets the MySQL Foreign Key Definition for an ForeignKey object.
     *
     * @param ForeignKey $foreignKey
     * @param string     $tableName  Table name
     * @return string
     */
    protected function getForeignKeySqlDefinition(ForeignKey $foreignKey, $tableName)
    {
        $constraintName = $foreignKey->getConstraint() ?: $tableName . '_' . implode('_', $foreignKey->getColumns());
        $def = ' CONSTRAINT "' . $constraintName . '" FOREIGN KEY ("' . implode('", "', $foreignKey->getColumns()) . '")';
        $def .= " REFERENCES {$this->quoteTableName($foreignKey->getReferencedTable()->getName())} (\"" . implode('", "', $foreignKey->getReferencedColumns()) . '")';
        if ($foreignKey->getOnDelete()) {
            $def .= " ON DELETE {$foreignKey->getOnDelete()}";
        }
        if ($foreignKey->getOnUpdate()) {
            $def .= " ON UPDATE {$foreignKey->getOnUpdate()}";
        }
        return $def;
    }

    /**
     * {@inheritdoc}
     */
    public function createSchemaTable()
    {
        // Create the public/custom schema if it doesn't already exist
        if (false === $this->hasSchema($this->getSchemaName())) {
            $this->createSchema($this->getSchemaName());
        }

        $this->fetchAll(sprintf('SET search_path TO %s', $this->getSchemaName()));

        return parent::createSchemaTable();
    }

    /**
     * Creates the specified schema.
     *
     * @param  string $schemaName Schema Name
     * @return void
     */
    public function createSchema($schemaName = 'public')
    {
        $this->startCommandTimer();
        $this->writeCommand('addSchema', array($schemaName));
        $sql = sprintf('CREATE SCHEMA %s;', $this->quoteSchemaName($schemaName)); // from postgres 9.3 we can use "CREATE SCHEMA IF NOT EXISTS schema_name"
        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * Checks to see if a schema exists.
     *
     * @param string $schemaName Schema Name
     * @return boolean
     */
    public function hasSchema($schemaName)
    {
        $sql = sprintf(
            "SELECT count(*)
             FROM pg_namespace
             WHERE nspname = '%s'",
            $schemaName
        );
        $result = $this->fetchRow($sql);
        return $result['count'] > 0;
    }

    /**
     * Drops the specified schema table.
     *
     * @param string $schemaName Schema name
     * @return void
     */
    public function dropSchema($schemaName)
    {
        $this->startCommandTimer();
        $this->writeCommand('dropSchema', array($schemaName));
        $sql = sprintf("DROP SCHEMA IF EXISTS %s CASCADE;", $this->quoteSchemaName($schemaName));
        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * Drops all schemas.
     *
     * @return void
     */
    public function dropAllSchemas()
    {
        $this->startCommandTimer();
        $this->writeCommand('dropAllSchemas');
        foreach ($this->getAllSchemas() as $schema) {
            $this->dropSchema($schema);
        }
        $this->endCommandTimer();
    }

    /**
     * Returns schemas.
     *
     * @return array
     */
    public function getAllSchemas()
    {
        $sql = "SELECT schema_name
                FROM information_schema.schemata
                WHERE schema_name <> 'information_schema' AND schema_name !~ '^pg_'";
        $items = $this->fetchAll($sql);
        $schemaNames = array();
        foreach ($items as $item) {
            $schemaNames[] = $item['schema_name'];
        }
        return $schemaNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnTypes()
    {
        return array_merge(parent::getColumnTypes(), array('json', 'jsonb'));
    }

    /**
     * {@inheritdoc}
     */
    public function isValidColumnType(Column $column)
    {
        // If not a standard column type, maybe it is array type?
        return (parent::isValidColumnType($column) || $this->isArrayType($column->getType()));
    }

    /**
     * Check if the given column is an array of a valid type.
     *
     * @param  string $columnType
     * @return bool
     */
    protected function isArrayType($columnType)
    {
        if (!preg_match('/^([a-z]+)(?:\[\]){1,}$/', $columnType, $matches)) {
            return false;
        }

        $baseType = $matches[1];
        return in_array($baseType, $this->getColumnTypes());
    }

    /**
     * Gets the schema name.
     *
     * @return string
     */
    private function getSchemaName()
    {
        $options = $this->getOptions();
        return empty($options['schema']) ? 'public' : $options['schema'];
    }

    /**
     * Cast a value to a boolean appropriate for the adapter.
     *
     * @param mixed $value The value to be cast
     *
     * @return mixed
     */
    public function castToBool($value)
    {
        return (bool) $value ? 'TRUE' : 'FALSE';
    }
}
