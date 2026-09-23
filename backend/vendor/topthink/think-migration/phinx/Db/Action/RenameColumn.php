<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Action;

use Phinx\Db\Table\Column;
use Phinx\Db\Table\Table;

class RenameColumn extends Action
{
    /**
     * The column to be renamed
     *
     * @var \Phinx\Db\Table\Column
     */
    protected $column;

    /**
     * The new name for the column
     *
     * @var string
     */
    protected $newName;

    /**
     * Constructor
     *
     * @param \Phinx\Db\Table\Table $table The table where the column is
     * @param \Phinx\Db\Table\Column $column The column to be renamed
     * @param string $newName The new name for the column
     */
    public function __construct(Table $table, Column $column, string $newName)
    {
        parent::__construct($table);
        $this->newName = $newName;
        $this->column = $column;
    }

    /**
     * Creates a new RenameColumn object after building the passed
     * arguments
     *
     * @param \Phinx\Db\Table\Table $table The table where the column is
     * @param string $columnName The name of the column to be changed
     * @param string $newName The new name for the column
     * @return static
     */
    public static function build(Table $table, string $columnName, string $newName)
    {
        $column = new Column();
        $column->setName($columnName);

        return new static($table, $column, $newName);
    }

    /**
     * Returns the column to be changed
     *
     * @return \Phinx\Db\Table\Column
     */
    public function getColumn(): Column
    {
        return $this->column;
    }

    /**
     * Returns the new name for the column
     *
     * @return string
     */
    public function getNewName(): string
    {
        return $this->newName;
    }
}
