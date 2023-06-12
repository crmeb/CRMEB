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

/**
 * Phinx SQLite Adapter.
 *
 * @author Rob Morgan <robbym@gmail.com>
 * @author Richard McIntyre <richard.mackstars@gmail.com>
 */
class SQLiteAdapter extends PdoAdapter implements AdapterInterface
{
    protected $definitionsWithLimits = array(
        'CHARACTER',
        'VARCHAR',
        'VARYING CHARACTER',
        'NCHAR',
        'NATIVE CHARACTER',
        'NVARCHAR'
    );

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if (null === $this->connection) {
            if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
                // @codeCoverageIgnoreStart
                throw new \RuntimeException('You need to enable the PDO_SQLITE extension for Phinx to run properly.');
                // @codeCoverageIgnoreEnd
            }

            $db = null;
            $options = $this->getOptions();

            // if port is specified use it, otherwise use the MySQL default
            if (isset($options['memory'])) {
                $dsn = 'sqlite::memory:';
            } else {
                $dsn = 'sqlite:' . $options['name'];
                if (file_exists($options['name'] . '.sqlite3')) {
                    $dsn = 'sqlite:' . $options['name'] . '.sqlite3';
                }
            }

            try {
                $db = new \PDO($dsn);
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
        $this->execute('BEGIN TRANSACTION');
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
     * {@inheritdoc}
     */
    public function quoteTableName($tableName)
    {
        return str_replace('.', '`.`', $this->quoteColumnName($tableName));
    }

    /**
     * {@inheritdoc}
     */
    public function quoteColumnName($columnName)
    {
        return '`' . str_replace('`', '``', $columnName) . '`';
    }

    /**
     * {@inheritdoc}
     */
    public function hasTable($tableName)
    {
        $tables = array();
        $rows = $this->fetchAll(sprintf('SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'%s\'', $tableName));
        foreach ($rows as $row) {
            $tables[] = strtolower($row[0]);
        }

        return in_array(strtolower($tableName), $tables);
    }

    /**
     * {@inheritdoc}
     */
    public function createTable(Table $table)
    {
        $this->startCommandTimer();

        // Add the default primary key
        $columns = $table->getPendingColumns();
        $options = $table->getOptions();
        if (!isset($options['id']) || (isset($options['id']) && $options['id'] === true)) {
            $column = new Column();
            $column->setName('id')
                   ->setType('integer')
                   ->setIdentity(true);

            array_unshift($columns, $column);

        } elseif (isset($options['id']) && is_string($options['id'])) {
            // Handle id => "field_name" to support AUTO_INCREMENT
            $column = new Column();
            $column->setName($options['id'])
                   ->setType('integer')
                   ->setIdentity(true);

            array_unshift($columns, $column);
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
            if (is_string($options['primary_key'])) {       // handle primary_key => 'id'
                $sql .= $this->quoteColumnName($options['primary_key']);
            } elseif (is_array($options['primary_key'])) { // handle primary_key => array('tag_id', 'resource_id')
                // PHP 5.4 will allow access of $this, so we can call quoteColumnName() directly in the anonymous function,
                // but for now just hard-code the adapter quotes
                $sql .= implode(
                    ',',
                    array_map(
                        function ($v) {
                            return '`' . $v . '`';
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
                $sql .= ', ' . $this->getForeignKeySqlDefinition($foreignKey);
            }
        }

        $sql = rtrim($sql) . ');';
        // execute the sql
        $this->writeCommand('createTable', array($table->getName()));
        $this->execute($sql);
        $this->endCommandTimer();

        foreach ($table->getIndexes() as $index) {
            $this->addIndex($table, $index);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renameTable($tableName, $newTableName)
    {
        $this->startCommandTimer();
        $this->writeCommand('renameTable', array($tableName, $newTableName));
        $this->execute(sprintf('ALTER TABLE %s RENAME TO %s', $this->quoteTableName($tableName), $this->quoteTableName($newTableName)));
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
        $rows = $this->fetchAll(sprintf('pragma table_info(%s)', $this->quoteTableName($tableName)));

        foreach ($rows as $columnInfo) {
            $column = new Column();
            $type = strtolower($columnInfo['type']);
            $column->setName($columnInfo['name'])
                   ->setNull($columnInfo['notnull'] !== '1')
                   ->setDefault($columnInfo['dflt_value']);

            $phinxType = $this->getPhinxType($type);
            $column->setType($phinxType['name'])
                   ->setLimit($phinxType['limit']);

            if ($columnInfo['pk'] == 1) {
                $column->setIdentity(true);
            }

            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function hasColumn($tableName, $columnName)
    {
        $rows = $this->fetchAll(sprintf('pragma table_info(%s)', $this->quoteTableName($tableName)));
        foreach ($rows as $column) {
            if (strcasecmp($column['name'], $columnName) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn(Table $table, Column $column)
    {
        $this->startCommandTimer();

        $sql = sprintf(
            'ALTER TABLE %s ADD COLUMN %s %s',
            $this->quoteTableName($table->getName()),
            $this->quoteColumnName($column->getName()),
            $this->getColumnSqlDefinition($column)
        );

        $this->writeCommand('addColumn', array($table->getName(), $column->getName(), $column->getType()));
        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function renameColumn($tableName, $columnName, $newColumnName)
    {
        $tmpTableName = 'tmp_' . $tableName;

        $rows = $this->fetchAll('select * from sqlite_master where `type` = \'table\'');

        $sql = '';
        foreach ($rows as $table) {
            if ($table['tbl_name'] === $tableName) {
                $sql = $table['sql'];
            }
        }

        $columns = $this->fetchAll(sprintf('pragma table_info(%s)', $this->quoteTableName($tableName)));
        $selectColumns = array();
        $writeColumns = array();
        foreach ($columns as $column) {
            $selectName = $column['name'];
            $writeName = ($selectName == $columnName) ? $newColumnName : $selectName;
            $selectColumns[] = $this->quoteColumnName($selectName);
            $writeColumns[] = $this->quoteColumnName($writeName);
        }

        if (!in_array($this->quoteColumnName($columnName), $selectColumns)) {
            throw new \InvalidArgumentException(sprintf(
                'The specified column doesn\'t exist: ' . $columnName
            ));
        }

        $this->execute(sprintf('ALTER TABLE %s RENAME TO %s', $tableName, $tmpTableName));

        $sql = str_replace(
            $this->quoteColumnName($columnName),
            $this->quoteColumnName($newColumnName),
            $sql
        );
        $this->execute($sql);

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s',
            $tableName,
            implode(', ', $writeColumns),
            implode(', ', $selectColumns),
            $tmpTableName
        );

        $this->execute($sql);

        $this->execute(sprintf('DROP TABLE %s', $this->quoteTableName($tmpTableName)));
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function changeColumn($tableName, $columnName, Column $newColumn)
    {

        // TODO: DRY this up....

        $this->startCommandTimer();
        $this->writeCommand('changeColumn', array($tableName, $columnName, $newColumn->getType()));

        $tmpTableName = 'tmp_' . $tableName;

        $rows = $this->fetchAll('select * from sqlite_master where `type` = \'table\'');

        $sql = '';
        foreach ($rows as $table) {
            if ($table['tbl_name'] === $tableName) {
                $sql = $table['sql'];
            }
        }

        $columns = $this->fetchAll(sprintf('pragma table_info(%s)', $this->quoteTableName($tableName)));
        $selectColumns = array();
        $writeColumns = array();
        foreach ($columns as $column) {
            $selectName = $column['name'];
            $writeName = ($selectName === $columnName) ? $newColumn->getName() : $selectName;
            $selectColumns[] = $this->quoteColumnName($selectName);
            $writeColumns[] = $this->quoteColumnName($writeName);
        }

        if (!in_array($this->quoteColumnName($columnName), $selectColumns)) {
            throw new \InvalidArgumentException(sprintf(
                'The specified column doesn\'t exist: ' . $columnName
            ));
        }

        $this->execute(sprintf('ALTER TABLE %s RENAME TO %s', $tableName, $tmpTableName));

        $sql = preg_replace(
            sprintf("/%s[^,]+([,)])/", $this->quoteColumnName($columnName)),
            sprintf('%s %s$1', $this->quoteColumnName($newColumn->getName()), $this->getColumnSqlDefinition($newColumn)),
            $sql,
            1
        );

        $this->execute($sql);

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s',
            $tableName,
            implode(', ', $writeColumns),
            implode(', ', $selectColumns),
            $tmpTableName
        );

        $this->execute($sql);
        $this->execute(sprintf('DROP TABLE %s', $this->quoteTableName($tmpTableName)));
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function dropColumn($tableName, $columnName)
    {
        // TODO: DRY this up....

        $this->startCommandTimer();
        $this->writeCommand('dropColumn', array($tableName, $columnName));

        $tmpTableName = 'tmp_' . $tableName;

        $rows = $this->fetchAll('select * from sqlite_master where `type` = \'table\'');

        $sql = '';
        foreach ($rows as $table) {
            if ($table['tbl_name'] === $tableName) {
                $sql = $table['sql'];
            }
        }

        $rows = $this->fetchAll(sprintf('pragma table_info(%s)', $this->quoteTableName($tableName)));
        $columns = array();
        $columnType = null;
        foreach ($rows as $row) {
            if ($row['name'] !== $columnName) {
                $columns[] = $row['name'];
            } else {
                $found = true;
                $columnType = $row['type'];
            }
        }

        if (!isset($found)) {
            throw new \InvalidArgumentException(sprintf(
                'The specified column doesn\'t exist: ' . $columnName
            ));
        }

        $this->execute(sprintf('ALTER TABLE %s RENAME TO %s', $tableName, $tmpTableName));

        $sql = preg_replace(
            sprintf("/%s\s%s[^,)]*(,\s|\))/", preg_quote($this->quoteColumnName($columnName)), preg_quote($columnType)),
            "",
            $sql
        );

        if (substr($sql, -2) === ', ') {
            $sql = substr($sql, 0, -2) . ')';
        }

        $this->execute($sql);

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s',
            $tableName,
            implode(', ', $columns),
            implode(', ', $columns),
            $tmpTableName
        );

        $this->execute($sql);
        $this->execute(sprintf('DROP TABLE %s', $this->quoteTableName($tmpTableName)));
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
        $rows = $this->fetchAll(sprintf('pragma index_list(%s)', $tableName));

        foreach ($rows as $row) {
            $indexData = $this->fetchAll(sprintf('pragma index_info(%s)', $row['name']));
            if (!isset($indexes[$tableName])) {
                $indexes[$tableName] = array('index' => $row['name'], 'columns' => array());
            }
            foreach ($indexData as $indexItem) {
                $indexes[$tableName]['columns'][] = strtolower($indexItem['name']);
            }
        }
        return $indexes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIndex($tableName, $columns)
    {
        if (is_string($columns)) {
            $columns = array($columns); // str to array
        }

        $columns = array_map('strtolower', $columns);
        $indexes = $this->getIndexes($tableName);

        foreach ($indexes as $index) {
            $a = array_diff($columns, $index['columns']);
            if (empty($a)) {
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

        foreach ($indexes as $index) {
            if ($indexName === $index['index']) {
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
        $indexColumnArray = array();
        foreach ($index->getColumns() as $column) {
            $indexColumnArray []= sprintf('`%s` ASC', $column);
        }
        $indexColumns = implode(',', $indexColumnArray);
        $this->execute(
            sprintf(
                'CREATE %s ON %s (%s)',
                $this->getIndexSqlDefinition($table, $index),
                $this->quoteTableName($table->getName()),
                $indexColumns
            )
        );
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

        foreach ($indexes as $index) {
            $a = array_diff($columns, $index['columns']);
            if (empty($a)) {
                $this->execute(
                    sprintf(
                        'DROP INDEX %s',
                        $this->quoteColumnName($index['index'])
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
        $indexes = $this->getIndexes($tableName);

        foreach ($indexes as $index) {
            if ($indexName === $index['index']) {
                $this->execute(
                    sprintf(
                        'DROP INDEX %s',
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
    public function hasForeignKey($tableName, $columns, $constraint = null)
    {
        if (is_string($columns)) {
            $columns = array($columns); // str to array
        }
        $foreignKeys = $this->getForeignKeys($tableName);

        $a = array_diff($columns, $foreignKeys);
        if (empty($a)) {
            return true;
        }
        return false;
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
        $rows = $this->fetchAll(
            "SELECT sql, tbl_name
              FROM (
                    SELECT sql sql, type type, tbl_name tbl_name, name name
                      FROM sqlite_master
                     UNION ALL
                    SELECT sql, type, tbl_name, name
                      FROM sqlite_temp_master
                   )
             WHERE type != 'meta'
               AND sql NOTNULL
               AND name NOT LIKE 'sqlite_%'
             ORDER BY substr(type, 2, 1), name"
        );

        foreach ($rows as $row) {
            if ($row['tbl_name'] === $tableName) {

                if (strpos($row['sql'], 'REFERENCES') !== false) {
                    preg_match_all("/\(`([^`]*)`\) REFERENCES/", $row['sql'], $matches);
                    foreach ($matches[1] as $match) {
                        $foreignKeys[] = $match;
                    }
                }
            }
        }
        return $foreignKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function addForeignKey(Table $table, ForeignKey $foreignKey)
    {
        // TODO: DRY this up....
        $this->startCommandTimer();
        $this->writeCommand('addForeignKey', array($table->getName(), $foreignKey->getColumns()));
        $this->execute('pragma foreign_keys = ON');

        $tmpTableName = 'tmp_' . $table->getName();
        $rows = $this->fetchAll('select * from sqlite_master where `type` = \'table\'');

        $sql = '';
        foreach ($rows as $row) {
            if ($row['tbl_name'] === $table->getName()) {
                $sql = $row['sql'];
            }
        }

        $rows = $this->fetchAll(sprintf('pragma table_info(%s)', $this->quoteTableName($table->getName())));
        $columns = array();
        foreach ($rows as $column) {
            $columns[] = $this->quoteColumnName($column['name']);
        }

        $this->execute(sprintf('ALTER TABLE %s RENAME TO %s', $this->quoteTableName($table->getName()), $tmpTableName));

        $sql = substr($sql, 0, -1) . ',' . $this->getForeignKeySqlDefinition($foreignKey) . ')';
        $this->execute($sql);

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s',
            $this->quoteTableName($table->getName()),
            implode(', ', $columns),
            implode(', ', $columns),
            $this->quoteTableName($tmpTableName)
        );

        $this->execute($sql);
        $this->execute(sprintf('DROP TABLE %s', $this->quoteTableName($tmpTableName)));
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function dropForeignKey($tableName, $columns, $constraint = null)
    {
        // TODO: DRY this up....

        $this->startCommandTimer();
        if (is_string($columns)) {
            $columns = array($columns); // str to array
        }

        $this->writeCommand('dropForeignKey', array($tableName, $columns));

        $tmpTableName = 'tmp_' . $tableName;

        $rows = $this->fetchAll('select * from sqlite_master where `type` = \'table\'');

        $sql = '';
        foreach ($rows as $table) {
            if ($table['tbl_name'] === $tableName) {
                $sql = $table['sql'];
            }
        }

        $rows = $this->fetchAll(sprintf('pragma table_info(%s)', $this->quoteTableName($tableName)));
        $replaceColumns = array();
        foreach ($rows as $row) {
            if (!in_array($row['name'], $columns)) {
                $replaceColumns[] = $row['name'];
            } else {
                $found = true;
            }
        }

        if (!isset($found)) {
            throw new \InvalidArgumentException(sprintf(
                'The specified column doesn\'t exist: '
            ));
        }

        $this->execute(sprintf('ALTER TABLE %s RENAME TO %s', $this->quoteTableName($tableName), $tmpTableName));

        foreach ($columns as $columnName) {
            $search = sprintf(
                "/,[^,]*\(%s(?:,`?(.*)`?)?\) REFERENCES[^,]*\([^\)]*\)[^,)]*/",
                $this->quoteColumnName($columnName)
            );
            $sql = preg_replace($search, '', $sql, 1);
        }

        $this->execute($sql);

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s',
            $tableName,
            implode(', ', $columns),
            implode(', ', $columns),
            $tmpTableName
        );

        $this->execute($sql);
        $this->execute(sprintf('DROP TABLE %s', $this->quoteTableName($tmpTableName)));
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Table $table, $row)
    {
        $this->startCommandTimer();
        $this->writeCommand('insert', array($table->getName()));

        $sql = sprintf(
            "INSERT INTO %s ",
            $this->quoteTableName($table->getName())
        );

        $columns = array_keys($row);
        $sql .= "(". implode(', ', array_map(array($this, 'quoteColumnName'), $columns)) . ")";
        $sql .= " VALUES ";

        $sql .= "(" . implode(', ', array_map(function ($value) {
                if (is_numeric($value)) {
                    return $value;
                }
                return "'{$value}'";
            }, $row)) . ")";

        $this->execute($sql);
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlType($type, $limit = null)
    {
        switch ($type) {
            case static::PHINX_TYPE_STRING:
                return array('name' => 'varchar', 'limit' => 255);
                break;
            case static::PHINX_TYPE_CHAR:
                return array('name' => 'char', 'limit' => 255);
                break;
            case static::PHINX_TYPE_TEXT:
                return array('name' => 'text');
                break;
            case static::PHINX_TYPE_INTEGER:
                return array('name' => 'integer');
                break;
            case static::PHINX_TYPE_BIG_INTEGER:
                return array('name' => 'bigint');
                break;
            case static::PHINX_TYPE_FLOAT:
                return array('name' => 'float');
                break;
            case static::PHINX_TYPE_DECIMAL:
                return array('name' => 'decimal');
                break;
            case static::PHINX_TYPE_DATETIME:
                return array('name' => 'datetime');
                break;
            case static::PHINX_TYPE_TIMESTAMP:
                return array('name' => 'datetime');
                break;
            case static::PHINX_TYPE_TIME:
                return array('name' => 'time');
                break;
            case static::PHINX_TYPE_DATE:
                return array('name' => 'date');
                break;
            case static::PHINX_TYPE_BLOB:
            case static::PHINX_TYPE_BINARY:
                return array('name' => 'blob');
                break;
            case static::PHINX_TYPE_BOOLEAN:
                return array('name' => 'boolean');
                break;
            case static::PHINX_TYPE_UUID:
                return array('name' => 'char', 'limit' => 36);
            case static::PHINX_TYPE_ENUM:
                return array('name' => 'enum');
            // Geospatial database types
            // No specific data types exist in SQLite, instead all geospatial
            // functionality is handled in the client. See also: SpatiaLite.
            case static::PHINX_TYPE_GEOMETRY:
            case static::PHINX_TYPE_POLYGON:
                return array('name' => 'text');
                return;
            case static::PHINX_TYPE_LINESTRING:
                return array('name' => 'varchar', 'limit' => 255);
                break;
            case static::PHINX_TYPE_POINT:
                return array('name' => 'float');
            default:
                throw new \RuntimeException('The type: "' . $type . '" is not supported.');
        }
    }

    /**
     * Returns Phinx type by SQL type
     *
     * @param string $sqlTypeDef SQL type
     * @returns string Phinx type
     */
    public function getPhinxType($sqlTypeDef)
    {
        if (!preg_match('/^([\w]+)(\(([\d]+)*(,([\d]+))*\))*$/', $sqlTypeDef, $matches)) {
            throw new \RuntimeException('Column type ' . $sqlTypeDef . ' is not supported');
        } else {
            $limit = null;
            $precision = null;
            $type = $matches[1];
            if (count($matches) > 2) {
                $limit = $matches[3] ? $matches[3] : null;
            }
            if (count($matches) > 4) {
                $precision = $matches[5];
            }
            switch ($matches[1]) {
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
                case 'int':
                    $type = static::PHINX_TYPE_INTEGER;
                    if ($limit === 11) {
                        $limit = null;
                    }
                    break;
                case 'bigint':
                    if ($limit === 11) {
                        $limit = null;
                    }
                    $type = static::PHINX_TYPE_BIG_INTEGER;
                    break;
                case 'blob':
                    $type = static::PHINX_TYPE_BINARY;
                    break;
            }
            if ($type === 'tinyint') {
                if ($matches[3] === 1) {
                    $type = static::PHINX_TYPE_BOOLEAN;
                    $limit = null;
                }
            }

            $this->getSqlType($type);

            return array(
                'name' => $type,
                'limit' => $limit,
                'precision' => $precision
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createDatabase($name, $options = array())
    {
        $this->startCommandTimer();
        $this->writeCommand('createDatabase', array($name));
        touch($name . '.sqlite3');
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function hasDatabase($name)
    {
        return is_file($name . '.sqlite3');
    }

    /**
     * {@inheritdoc}
     */
    public function dropDatabase($name)
    {
        $this->startCommandTimer();
        $this->writeCommand('dropDatabase', array($name));
        if (file_exists($name . '.sqlite3')) {
            unlink($name . '.sqlite3');
        }
        $this->endCommandTimer();
    }

    /**
     * Get the definition for a `DEFAULT` statement.
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
        return isset($default) ? ' DEFAULT ' . $default : '';
    }

    /**
     * Gets the SQLite Column Definition for a Column object.
     *
     * @param Column $column Column
     * @return string
     */
    protected function getColumnSqlDefinition(Column $column)
    {
        $sqlType = $this->getSqlType($column->getType());
        $def = '';
        $def .= strtoupper($sqlType['name']);
        if ($column->getPrecision() && $column->getScale()) {
            $def .= '(' . $column->getPrecision() . ',' . $column->getScale() . ')';
        }
        $limitable = in_array(strtoupper($sqlType['name']), $this->definitionsWithLimits);
        if (($column->getLimit() || isset($sqlType['limit'])) && $limitable) {
            $def .= '(' . ($column->getLimit() ? $column->getLimit() : $sqlType['limit']) . ')';
        }
        if (($values = $column->getValues()) && is_array($values)) {
            $def .= " CHECK({$column->getName()} IN ('" . implode("', '", $values) . "'))";
        }

        $default = $column->getDefault();

        $def .= ($column->isNull() || is_null($default)) ? ' NULL' : ' NOT NULL';
        $def .= $this->getDefaultValueDefinition($default);
        $def .= ($column->isIdentity()) ? ' PRIMARY KEY AUTOINCREMENT' : '';

        if ($column->getUpdate()) {
            $def .= ' ON UPDATE ' . $column->getUpdate();
        }

        $def .= $this->getCommentDefinition($column);

        return $def;
    }

    /**
     * Gets the comment Definition for a Column object.
     *
     * @param Column $column Column
     * @return string
     */
    protected function getCommentDefinition(Column $column)
    {
        if ($column->getComment()) {
            return ' /* ' . $column->getComment() . ' */ ';
        }
        return '';
    }

    /**
     * Gets the SQLite Index Definition for an Index object.
     *
     * @param Index $index Index
     * @return string
     */
    protected function getIndexSqlDefinition(Table $table, Index $index)
    {
        if ($index->getType() === Index::UNIQUE) {
            $def = 'UNIQUE INDEX';
        } else {
            $def = 'INDEX';
        }
        if (is_string($index->getName())) {
            $indexName = $index->getName();
        } else {
            $indexName = $table->getName() . '_';
            foreach ($index->getColumns() as $column) {
                $indexName .= $column . '_';
            }
            $indexName .= 'index';
        }
        $def .= ' `' . $indexName . '`';
        return $def;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnTypes()
    {
        return array_merge(parent::getColumnTypes(), array('enum'));
    }

    /**
     * Gets the SQLite Foreign Key Definition for an ForeignKey object.
     *
     * @param ForeignKey $foreignKey
     * @return string
     */
    protected function getForeignKeySqlDefinition(ForeignKey $foreignKey)
    {
        $def = '';
        if ($foreignKey->getConstraint()) {
            $def .= ' CONSTRAINT ' . $this->quoteColumnName($foreignKey->getConstraint());
        } else {
            $columnNames = array();
            foreach ($foreignKey->getColumns() as $column) {
                $columnNames[] = $this->quoteColumnName($column);
            }
            $def .= ' FOREIGN KEY (' . implode(',', $columnNames) . ')';
            $refColumnNames = array();
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
        }
        return $def;
    }
}
