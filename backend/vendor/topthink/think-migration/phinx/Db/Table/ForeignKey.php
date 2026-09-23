<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Table;

use InvalidArgumentException;
use RuntimeException;

class ForeignKey
{
    public const CASCADE = 'CASCADE';
    public const RESTRICT = 'RESTRICT';
    public const SET_NULL = 'SET NULL';
    public const NO_ACTION = 'NO ACTION';

    /**
     * @var array<string>
     */
    protected static $validOptions = ['delete', 'update', 'constraint'];

    /**
     * @var string[]
     */
    protected $columns = [];

    /**
     * @var \Phinx\Db\Table\Table
     */
    protected $referencedTable;

    /**
     * @var string[]
     */
    protected $referencedColumns = [];

    /**
     * @var string|null
     */
    protected $onDelete;

    /**
     * @var string|null
     */
    protected $onUpdate;

    /**
     * @var string|null
     */
    protected $constraint;

    /**
     * Sets the foreign key columns.
     *
     * @param string[]|string $columns Columns
     * @return $this
     */
    public function setColumns($columns)
    {
        $this->columns = is_string($columns) ? [$columns] : $columns;

        return $this;
    }

    /**
     * Gets the foreign key columns.
     *
     * @return string[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Sets the foreign key referenced table.
     *
     * @param \Phinx\Db\Table\Table $table The table this KEY is pointing to
     * @return $this
     */
    public function setReferencedTable(Table $table)
    {
        $this->referencedTable = $table;

        return $this;
    }

    /**
     * Gets the foreign key referenced table.
     *
     * @return \Phinx\Db\Table\Table
     */
    public function getReferencedTable(): Table
    {
        return $this->referencedTable;
    }

    /**
     * Sets the foreign key referenced columns.
     *
     * @param string[] $referencedColumns Referenced columns
     * @return $this
     */
    public function setReferencedColumns(array $referencedColumns)
    {
        $this->referencedColumns = $referencedColumns;

        return $this;
    }

    /**
     * Gets the foreign key referenced columns.
     *
     * @return string[]
     */
    public function getReferencedColumns(): array
    {
        return $this->referencedColumns;
    }

    /**
     * Sets ON DELETE action for the foreign key.
     *
     * @param string $onDelete On Delete
     * @return $this
     */
    public function setOnDelete(string $onDelete)
    {
        $this->onDelete = $this->normalizeAction($onDelete);

        return $this;
    }

    /**
     * Gets ON DELETE action for the foreign key.
     *
     * @return string|null
     */
    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    /**
     * Gets ON UPDATE action for the foreign key.
     *
     * @return string|null
     */
    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    /**
     * Sets ON UPDATE action for the foreign key.
     *
     * @param string $onUpdate On Update
     * @return $this
     */
    public function setOnUpdate(string $onUpdate)
    {
        $this->onUpdate = $this->normalizeAction($onUpdate);

        return $this;
    }

    /**
     * Sets constraint for the foreign key.
     *
     * @param string $constraint Constraint
     * @return $this
     */
    public function setConstraint(string $constraint)
    {
        $this->constraint = $constraint;

        return $this;
    }

    /**
     * Gets constraint name for the foreign key.
     *
     * @return string|null
     */
    public function getConstraint(): ?string
    {
        return $this->constraint;
    }

    /**
     * Utility method that maps an array of index options to this objects methods.
     *
     * @param array<string, mixed> $options Options
     * @throws \RuntimeException
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            if (!in_array($option, static::$validOptions, true)) {
                throw new RuntimeException(sprintf('"%s" is not a valid foreign key option.', $option));
            }

            // handle $options['delete'] as $options['update']
            if ($option === 'delete') {
                $this->setOnDelete($value);
            } elseif ($option === 'update') {
                $this->setOnUpdate($value);
            } else {
                $method = 'set' . ucfirst($option);
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * From passed value checks if it's correct and fixes if needed
     *
     * @param string $action Action
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function normalizeAction(string $action): string
    {
        $constantName = 'static::' . str_replace(' ', '_', strtoupper(trim($action)));
        if (!defined($constantName)) {
            throw new InvalidArgumentException('Unknown action passed: ' . $action);
        }

        return constant($constantName);
    }
}
