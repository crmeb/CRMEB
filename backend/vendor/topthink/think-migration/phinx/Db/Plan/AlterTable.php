<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Plan;

use Phinx\Db\Action\Action;
use Phinx\Db\Table\Table;

/**
 * A collection of ALTER actions for a single table
 */
class AlterTable
{
    /**
     * The table
     *
     * @var \Phinx\Db\Table\Table
     */
    protected $table;

    /**
     * The list of actions to execute
     *
     * @var \Phinx\Db\Action\Action[]
     */
    protected $actions = [];

    /**
     * Constructor
     *
     * @param \Phinx\Db\Table\Table $table The table to change
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Adds another action to the collection
     *
     * @param \Phinx\Db\Action\Action $action The action to add
     * @return void
     */
    public function addAction(Action $action): void
    {
        $this->actions[] = $action;
    }

    /**
     * Returns the table associated to this collection
     *
     * @return \Phinx\Db\Table\Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * Returns an array with all collected actions
     *
     * @return \Phinx\Db\Action\Action[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }
}
