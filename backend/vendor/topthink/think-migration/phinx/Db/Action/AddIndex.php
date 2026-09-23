<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Action;

use Phinx\Db\Table\Index;
use Phinx\Db\Table\Table;

class AddIndex extends Action
{
    /**
     * The index to add to the table
     *
     * @var \Phinx\Db\Table\Index
     */
    protected $index;

    /**
     * Constructor
     *
     * @param \Phinx\Db\Table\Table $table The table to add the index to
     * @param \Phinx\Db\Table\Index $index The index to be added
     */
    public function __construct(Table $table, Index $index)
    {
        parent::__construct($table);
        $this->index = $index;
    }

    /**
     * Creates a new AddIndex object after building the index object with the
     * provided arguments
     *
     * @param \Phinx\Db\Table\Table $table The table to add the index to
     * @param string|string[]|\Phinx\Db\Table\Index $columns The columns to index
     * @param array<string, mixed> $options Additional options for the index creation
     * @return static
     */
    public static function build(Table $table, $columns, array $options = [])
    {
        // create a new index object if strings or an array of strings were supplied
        $index = $columns;

        if (!$columns instanceof Index) {
            $index = new Index();

            $index->setColumns($columns);
            $index->setOptions($options);
        }

        return new static($table, $index);
    }

    /**
     * Returns the index to be added
     *
     * @return \Phinx\Db\Table\Index
     */
    public function getIndex(): Index
    {
        return $this->index;
    }
}
