<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

use Exception;
use InvalidArgumentException;
use Phinx\Db\Table;
use Phinx\Db\Table\Column;
use Phinx\Util\Literal;
use think\console\Input as InputInterface;
use think\migration\NullOutput;
use think\console\Output as OutputInterface;

/**
 * Base Abstract Database Adapter.
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var array<string, mixed>
     */
    protected $options = [];

    /**
     * @var \think\console\Input|null
     */
    protected $input;

    /**
     * @var \think\console\Output
     */
    protected $output;

    /**
     * @var string[]
     */
    protected $createdTables = [];

    /**
     * @var string
     */
    protected $schemaTableName = 'phinxlog';

    /**
     * @var array
     */
    protected $dataDomain = [];

    /**
     * Class Constructor.
     *
     * @param array<string, mixed> $options Options
     * @param \think\console\Input|null $input Input Interface
     * @param \think\console\Output|null $output Output Interface
     */
    public function __construct(array $options, ?InputInterface $input = null, ?OutputInterface $output = null)
    {
        $this->setOptions($options);
        if ($input !== null) {
            $this->setInput($input);
        }
        if ($output !== null) {
            $this->setOutput($output);
        }
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): AdapterInterface
    {
        $this->options = $options;

        if (isset($options['default_migration_table'])) {
            trigger_error('The default_migration_table setting for adapter has been deprecated since 0.13.0. Use `migration_table` instead.', E_USER_DEPRECATED);
            if (!isset($options['migration_table'])) {
                $options['migration_table'] = $options['default_migration_table'];
            }
        }

        if (isset($options['migration_table'])) {
            $this->setSchemaTableName($options['migration_table']);
        }

        if (isset($options['data_domain'])) {
            $this->setDataDomain($options['data_domain']);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getOption(string $name)
    {
        if (!$this->hasOption($name)) {
            return null;
        }

        return $this->options[$name];
    }

    /**
     * @inheritDoc
     */
    public function setInput(InputInterface $input): AdapterInterface
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInput(): ?InputInterface
    {
        return $this->input;
    }

    /**
     * @inheritDoc
     */
    public function setOutput(OutputInterface $output): AdapterInterface
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOutput(): OutputInterface
    {
        if ($this->output === null) {
            $output = new NullOutput();
            $this->setOutput($output);
        }

        return $this->output;
    }

    /**
     * @inheritDoc
     * @return array<int>
     */
    public function getVersions(): array
    {
        $rows = $this->getVersionLog();

        return array_keys($rows);
    }

    /**
     * Gets the schema table name.
     *
     * @return string
     */
    public function getSchemaTableName(): string
    {
        return $this->schemaTableName;
    }

    /**
     * Sets the schema table name.
     *
     * @param string $schemaTableName Schema Table Name
     * @return $this
     */
    public function setSchemaTableName(string $schemaTableName)
    {
        $this->schemaTableName = $schemaTableName;

        return $this;
    }

    /**
     * Gets the data domain.
     *
     * @return array
     */
    public function getDataDomain(): array
    {
        return $this->dataDomain;
    }

    /**
     * Sets the data domain.
     *
     * @param array $dataDomain Array for the data domain
     * @return $this
     */
    public function setDataDomain(array $dataDomain)
    {
        $this->dataDomain = [];

        // Iterate over data domain field definitions and perform initial and
        // simple normalization. We make sure the definition as a base 'type'
        // and it is compatible with the base Phinx types.
        foreach ($dataDomain as $type => $options) {
            if (!isset($options['type'])) {
                throw new \InvalidArgumentException(sprintf(
                    'You must specify a type for data domain type "%s".',
                    $type
                ));
            }

            // Replace type if it's the name of a Phinx constant
            if (defined('static::' . $options['type'])) {
                $options['type'] = constant('static::' . $options['type']);
            }

            if (!in_array($options['type'], $this->getColumnTypes(), true)) {
                throw new \InvalidArgumentException(sprintf(
                    'An invalid column type "%s" was specified for data domain type "%s".',
                    $options['type'],
                    $type
                ));
            }

            $internal_type = $options['type'];
            unset($options['type']);

            // Do a simple replacement for the 'length' / 'limit' option and
            // detect hinting values for 'limit'.
            if (isset($options['length'])) {
                $options['limit'] = $options['length'];
                unset($options['length']);
            }

            if (isset($options['limit']) && !is_numeric($options['limit'])) {
                if (!defined('static::' . $options['limit'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'An invalid limit value "%s" was specified for data domain type "%s".',
                        $options['limit'],
                        $type
                    ));
                }

                $options['limit'] = constant('static::' . $options['limit']);
            }

            // Save the data domain types in a more suitable format
            $this->dataDomain[$type] = [
                'type' => $internal_type,
                'options' => $options,
            ];
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getColumnForType(string $columnName, string $type, array $options): Column
    {
        $column = new Column();
        $column->setName($columnName);

        if (array_key_exists($type, $this->getDataDomain())) {
            $column->setType($this->dataDomain[$type]['type']);
            $column->setOptions($this->dataDomain[$type]['options']);
        } else {
            $column->setType($type);
        }

        $column->setOptions($options);

        return $column;
    }

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     * @return void
     */
    public function createSchemaTable(): void
    {
        try {
            $options = [
                'id' => false,
                'primary_key' => 'version',
            ];

            $table = new Table($this->getSchemaTableName(), $options, $this);
            $table->addColumn('version', 'biginteger', ['null' => false])
                ->addColumn('migration_name', 'string', ['limit' => 100, 'default' => null, 'null' => true])
                ->addColumn('start_time', 'timestamp', ['default' => null, 'null' => true])
                ->addColumn('end_time', 'timestamp', ['default' => null, 'null' => true])
                ->addColumn('breakpoint', 'boolean', ['default' => false, 'null' => false])
                ->save();
        } catch (Exception $exception) {
            throw new InvalidArgumentException(
                'There was a problem creating the schema table: ' . $exception->getMessage(),
                (int)$exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getAdapterType(): string
    {
        return $this->getOption('adapter');
    }

    /**
     * @inheritDoc
     */
    public function isValidColumnType(Column $column): bool
    {
        return $column->getType() instanceof Literal || in_array($column->getType(), $this->getColumnTypes(), true);
    }

    /**
     * Determines if instead of executing queries a dump to standard output is needed
     *
     * @return bool
     */
    public function isDryRunEnabled(): bool
    {
        /** @var \think\console\Input|null $input */
        $input = $this->getInput();

        return $input && $input->hasOption('dry-run') ? (bool)$input->getOption('dry-run') : false;
    }

    /**
     * Adds user-created tables (e.g. not phinxlog) to a cached list
     *
     * @param string $tableName The name of the table
     * @return void
     */
    protected function addCreatedTable(string $tableName): void
    {
        $tableName = $this->quoteTableName($tableName);
        if (substr_compare($tableName, 'phinxlog', -strlen('phinxlog')) !== 0) {
            $this->createdTables[] = $tableName;
        }
    }

    /**
     * Updates the name of the cached table
     *
     * @param string $tableName Original name of the table
     * @param string $newTableName New name of the table
     * @return void
     */
    protected function updateCreatedTableName(string $tableName, string $newTableName): void
    {
        $tableName = $this->quoteTableName($tableName);
        $newTableName = $this->quoteTableName($newTableName);
        $key = array_search($tableName, $this->createdTables, true);
        if ($key !== false) {
            $this->createdTables[$key] = $newTableName;
        }
    }

    /**
     * Removes table from the cached created list
     *
     * @param string $tableName The name of the table
     * @return void
     */
    protected function removeCreatedTable(string $tableName): void
    {
        $tableName = $this->quoteTableName($tableName);
        $key = array_search($tableName, $this->createdTables, true);
        if ($key !== false) {
            unset($this->createdTables[$key]);
        }
    }

    /**
     * Check if the table is in the cached list of created tables
     *
     * @param string $tableName The name of the table
     * @return bool
     */
    protected function hasCreatedTable(string $tableName): bool
    {
        $tableName = $this->quoteTableName($tableName);

        return in_array($tableName, $this->createdTables, true);
    }
}
