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

use think\console\Input as InputInterface;
use think\console\Output as OutputInterface;
use Phinx\Db\Table;
use Phinx\Db\Table\Column;
use Phinx\Migration\MigrationInterface;

/**
 * Phinx PDO Adapter.
 *
 * @author Rob Morgan <robbym@gmail.com>
 */
abstract class PdoAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $schemaTableName = 'migrations';

    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * @var float
     */
    protected $commandStartTime;

    /**
     * Class Constructor.
     *
     * @param array $options Options
     * @param InputInterface $input Input Interface
     * @param OutputInterface $output Output Interface
     */
    public function __construct(array $options, InputInterface $input = null, OutputInterface $output = null)
    {
        $this->setOptions($options);
        if (null !== $input) {
            $this->setInput($input);
        }
        if (null !== $output) {
            $this->setOutput($output);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        if (isset($options['default_migration_table'])) {
            $this->setSchemaTableName($options['default_migration_table']);
        }

        if (isset($options['connection'])) {
            $this->setConnection($options['connection']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        if (!$this->hasOption($name)) {
            return;
        }
        return $this->options[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        if (null === $this->output) {
            $output = new OutputInterface('nothing');
            $this->setOutput($output);
        }
        return $this->output;
    }

    /**
     * Sets the schema table name.
     *
     * @param string $schemaTableName Schema Table Name
     * @return PdoAdapter
     */
    public function setSchemaTableName($schemaTableName)
    {
        $this->schemaTableName = $schemaTableName;
        return $this;
    }

    /**
     * Gets the schema table name.
     *
     * @return string
     */
    public function getSchemaTableName()
    {
        return $this->schemaTableName;
    }

    /**
     * Sets the database connection.
     *
     * @param \PDO $connection Connection
     * @return AdapterInterface
     */
    public function setConnection(\PDO $connection)
    {
        $this->connection = $connection;

        // Create the schema table if it doesn't already exist
        if (!$this->hasSchemaTable()) {
            $this->createSchemaTable();
        } else {
            $table = new Table($this->getSchemaTableName(), array(), $this);
            if (!$table->hasColumn('migration_name')) {
                $table
                    ->addColumn('migration_name', 'string',
                        array('limit' => 100, 'after' => 'version', 'default' => null, 'null' => true)
                    )
                    ->save();
            }
            if (!$table->hasColumn('breakpoint')) {
                $table
                    ->addColumn('breakpoint', 'boolean', array('default' => false))
                    ->save();
            }
        }

        return $this;
    }

    /**
     * Gets the database connection
     *
     * @return \PDO
     */
    public function getConnection()
    {
        if (null === $this->connection) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Sets the command start time
     *
     * @param int $time
     * @return AdapterInterface
     */
    public function setCommandStartTime($time)
    {
        $this->commandStartTime = $time;
        return $this;
    }

    /**
     * Gets the command start time
     *
     * @return int
     */
    public function getCommandStartTime()
    {
        return $this->commandStartTime;
    }

    /**
     * Start timing a command.
     *
     * @return void
     */
    public function startCommandTimer()
    {
        $this->setCommandStartTime(microtime(true));
    }

    /**
     * Stop timing the current command and write the elapsed time to the
     * output.
     *
     * @return void
     */
    public function endCommandTimer()
    {
        $end = microtime(true);
        if (OutputInterface::VERBOSITY_VERBOSE <= $this->getOutput()->getVerbosity()) {
            $this->getOutput()->writeln('    -> ' . sprintf('%.4fs', $end - $this->getCommandStartTime()));
        }
    }

    /**
     * Write a Phinx command to the output.
     *
     * @param string $command Command Name
     * @param array  $args    Command Args
     * @return void
     */
    public function writeCommand($command, $args = array())
    {
        if (OutputInterface::VERBOSITY_VERBOSE <= $this->getOutput()->getVerbosity()) {
            if (count($args)) {
                $outArr = array();
                foreach ($args as $arg) {
                    if (is_array($arg)) {
                        $arg = array_map(function ($value) {
                            return '\'' . $value . '\'';
                        }, $arg);
                        $outArr[] = '[' . implode(', ', $arg)  . ']';
                        continue;
                    }

                    $outArr[] = '\'' . $arg . '\'';
                }
                $this->getOutput()->writeln(' -- ' . $command . '(' . implode(', ', $outArr) . ')');
                return;
            }
            $this->getOutput()->writeln(' -- ' . $command);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute($sql)
    {
        return $this->getConnection()->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function query($sql)
    {
        return $this->getConnection()->query($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRow($sql)
    {
        $result = $this->query($sql);
        return $result->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($sql)
    {
        $rows = array();
        $result = $this->query($sql);
        while ($row = $result->fetch()) {
            $rows[] = $row;
        }
        return $rows;
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
        $sql .= " VALUES (" . implode(', ', array_fill(0, count($columns), '?')) . ")";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(array_values($row));
        $this->endCommandTimer();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersions()
    {
        $rows = $this->getVersionLog();

        return array_keys($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionLog()
    {
        $result = array();
        $rows = $this->fetchAll(sprintf('SELECT * FROM %s ORDER BY version ASC', $this->getSchemaTableName()));
        foreach ($rows as $version) {
            $result[$version['version']] = $version;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function migrated(MigrationInterface $migration, $direction, $startTime, $endTime)
    {
        if (strcasecmp($direction, MigrationInterface::UP) === 0) {
            // up
            $sql = sprintf(
                "INSERT INTO %s (%s, %s, %s, %s, %s) VALUES ('%s', '%s', '%s', '%s', %s);",
                $this->getSchemaTableName(),
                $this->quoteColumnName('version'),
                $this->quoteColumnName('migration_name'),
                $this->quoteColumnName('start_time'),
                $this->quoteColumnName('end_time'),
                $this->quoteColumnName('breakpoint'),
                $migration->getVersion(),
                substr($migration->getName(), 0, 100),
                $startTime,
                $endTime,
                $this->castToBool(false)
            );

            $this->query($sql);
        } else {
            // down
            $sql = sprintf(
                "DELETE FROM %s WHERE %s = '%s'",
                $this->getSchemaTableName(),
                $this->quoteColumnName('version'),
                $migration->getVersion()
            );

            $this->query($sql);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toggleBreakpoint(MigrationInterface $migration)
    {
        $this->query(
            sprintf(
                'UPDATE %1$s SET %2$s = CASE %2$s WHEN %3$s THEN %4$s ELSE %3$s END WHERE %5$s = \'%6$s\';',
                $this->getSchemaTableName(),
                $this->quoteColumnName('breakpoint'),
                $this->castToBool(true),
                $this->castToBool(false),
                $this->quoteColumnName('version'),
                $migration->getVersion()
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resetAllBreakpoints()
    {
        return $this->execute(
            sprintf(
                'UPDATE %1$s SET %2$s = %3$s WHERE %2$s <> %3$s;',
                $this->getSchemaTableName(),
                $this->quoteColumnName('breakpoint'),
                $this->castToBool(false)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaTable()
    {
        return $this->hasTable($this->getSchemaTableName());
    }

    /**
     * {@inheritdoc}
     */
    public function createSchemaTable()
    {
        try {
            $options = array(
                'id'          => false,
                'primary_key' => 'version'
            );

            $table = new Table($this->getSchemaTableName(), $options, $this);

            if ($this->getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql'
                && version_compare($this->getConnection()->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.6.0', '>=')) {
                $table->addColumn('version', 'biginteger', array('limit' => 14))
                      ->addColumn('migration_name', 'string', array('limit' => 100, 'default' => null, 'null' => true))
                      ->addColumn('start_time', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
                      ->addColumn('end_time', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
                      ->addColumn('breakpoint', 'boolean', array('default' => false))
                      ->save();
            } else {
                $table->addColumn('version', 'biginteger')
                      ->addColumn('migration_name', 'string', array('limit' => 100, 'default' => null, 'null' => true))
                      ->addColumn('start_time', 'timestamp')
                      ->addColumn('end_time', 'timestamp')
                      ->addColumn('breakpoint', 'boolean', array('default' => false))
                      ->save();
            }
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException('There was a problem creating the schema table: ' . $exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapterType()
    {
        return $this->getOption('adapter');
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnTypes()
    {
        return array(
            'string',
            'char',
            'text',
            'integer',
            'biginteger',
            'float',
            'decimal',
            'datetime',
            'timestamp',
            'time',
            'date',
            'blob',
            'binary',
            'varbinary',
            'boolean',
            'uuid',
            // Geospatial data types
            'geometry',
            'point',
            'linestring',
            'polygon',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isValidColumnType(Column $column) {
        return in_array($column->getType(), $this->getColumnTypes());
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
        return (bool) $value ? 1 : 0;
    }
}
