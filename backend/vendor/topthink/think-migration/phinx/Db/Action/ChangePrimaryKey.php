<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Action;

use Phinx\Db\Table\Table;

class ChangePrimaryKey extends Action
{
    /**
     * The new columns for the primary key
     *
     * @var string|string[]|null
     */
    protected $newColumns;

    /**
     * Constructor
     *
     * @param \Phinx\Db\Table\Table $table The table to be changed
     * @param string|string[]|null $newColumns The new columns for the primary key
     */
    public function __construct(Table $table, $newColumns)
    {
        parent::__construct($table);
        $this->newColumns = $newColumns;
    }

    /**
     * Return the new columns for the primary key
     *
     * @return string|string[]|null
     */
    public function getNewColumns()
    {
        return $this->newColumns;
    }
}
