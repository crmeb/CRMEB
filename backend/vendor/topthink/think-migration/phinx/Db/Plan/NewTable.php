<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Plan;

use Phinx\Db\Table\Column;
use Phinx\Db\Table\Index;
use Phinx\Db\Table\Table;

/**
 * Represents the collection of actions for creating a new table
 */
class NewTable
{
    /**
     * The table to create
     *
     * @var \Phinx\Db\Table\Table
     */
    protected $table;

    /**
     * The list of columns to add
     *
     * @var \Phinx\Db\Table\Column[]
     */
    protected $columns = [];

    /**
     * The list of indexes to create
     *
     * @var \Phinx\Db\Table\Index[]
     */
    protected $indexes = [];

    /**
     * Constructor
     *
     * @param \Phinx\Db\Table\Table $table The table to create
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Adds a column to the collection
     *
     * @param \Phinx\Db\Table\Column $column The column description
     * @return void
     */
    public function addColumn(Column $column): void
    {
        $this->columns[] = $column;
    }

    /**
     * Adds an index to the collection
     *
     * @param \Phinx\Db\Table\Index $index The index description
     * @return void
     */
    public function addIndex(Index $index): void
    {
        $this->indexes[] = $index;
    }

    /**
     * Returns the table object associated to this collection
     *
     * @return \Phinx\Db\Table\Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * Returns the columns collection
     *
     * @return \Phinx\Db\Table\Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns the indexes collection
     *
     * @return \Phinx\Db\Table\Index[]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }
}
