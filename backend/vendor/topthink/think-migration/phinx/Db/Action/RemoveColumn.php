<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Action;

use Phinx\Db\Table\Column;
use Phinx\Db\Table\Table;

class RemoveColumn extends Action
{
    /**
     * The column to be removed
     *
     * @var \Phinx\Db\Table\Column
     */
    protected $column;

    /**
     * Constructor
     *
     * @param \Phinx\Db\Table\Table $table The table where the column is
     * @param \Phinx\Db\Table\Column $column The column to be removed
     */
    public function __construct(Table $table, Column $column)
    {
        parent::__construct($table);
        $this->column = $column;
    }

    /**
     * Creates a new RemoveColumn object after assembling the
     * passed arguments.
     *
     * @param \Phinx\Db\Table\Table $table The table where the column is
     * @param string $columnName The name of the column to drop
     * @return static
     */
    public static function build(Table $table, string $columnName)
    {
        $column = new Column();
        $column->setName($columnName);

        return new static($table, $column);
    }

    /**
     * Returns the column to be dropped
     *
     * @return \Phinx\Db\Table\Column
     */
    public function getColumn(): Column
    {
        return $this->column;
    }
}
