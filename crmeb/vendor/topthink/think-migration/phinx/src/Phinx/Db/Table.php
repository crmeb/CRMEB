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
 * @subpackage Phinx\Db
 */
namespace Phinx\Db;

use Phinx\Db\Table\Column;
use Phinx\Db\Table\Index;
use Phinx\Db\Table\ForeignKey;
use Phinx\Db\Adapter\AdapterInterface;

/**
 *
 * This object is based loosely on: http://api.rubyonrails.org/classes/ActiveRecord/ConnectionAdapters/Table.html.
 */
class Table
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var array
     */
    protected $indexes = array();

    /**
     * @var ForeignKey[]
     */
    protected $foreignKeys = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * Class Constuctor.
     *
     * @param string $name Table Name
     * @param array $options Options
     * @param AdapterInterface $adapter Database Adapter
     */
    public function __construct($name, $options = array(), AdapterInterface $adapter = null)
    {
        $this->setName($name);
        $this->setOptions($options);

        if (null !== $adapter) {
            $this->setAdapter($adapter);
        }
    }

    /**
     * Sets the table name.
     *
     * @param string $name Table Name
     * @return Table
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the table name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the table options.
     *
     * @param array $options
     * @return Table
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Gets the table options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the database adapter.
     *
     * @param AdapterInterface $adapter Database Adapter
     * @return Table
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Gets the database adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Does the table exist?
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->getAdapter()->hasTable($this->getName());
    }

    /**
     * Drops the database table.
     *
     * @return void
     */
    public function drop()
    {
        $this->getAdapter()->dropTable($this->getName());
    }

    /**
     * Renames the database table.
     *
     * @param string $newTableName New Table Name
     * @return Table
     */
    public function rename($newTableName)
    {
        $this->getAdapter()->renameTable($this->getName(), $newTableName);
        $this->setName($newTableName);
        return $this;
    }

    /**
     * Sets an array of columns waiting to be committed.
     * Use setPendingColumns
     *
     * @deprecated
     * @param array $columns Columns
     * @return Table
     */
    public function setColumns($columns)
    {
        $this->setPendingColumns($columns);
    }

    /**
     * Gets an array of the table columns.
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->getAdapter()->getColumns($this->getName());
    }

    /**
     * Sets an array of columns waiting to be committed.
     *
     * @param array $columns Columns
     * @return Table
     */
    public function setPendingColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Gets an array of columns waiting to be committed.
     *
     * @return Column[]
     */
    public function getPendingColumns()
    {
        return $this->columns;
    }

    /**
     * Sets an array of columns waiting to be indexed.
     *
     * @param array $indexes Indexes
     * @return Table
     */
    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;
        return $this;
    }

    /**
     * Gets an array of indexes waiting to be committed.
     *
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Sets an array of foreign keys waiting to be commited.
     *
     * @param ForeignKey[] $foreignKeys foreign keys
     * @return Table
     */
    public function setForeignKeys($foreignKeys)
    {
        $this->foreignKeys = $foreignKeys;
        return $this;
    }

    /**
     * Gets an array of foreign keys waiting to be commited.
     *
     * @return array|ForeignKey[]
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * Sets an array of data to be inserted.
     *
     * @param array $data Data
     * @return Table
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the data waiting to be inserted.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Resets all of the pending table changes.
     *
     * @return void
     */
    public function reset()
    {
        $this->setPendingColumns(array());
        $this->setIndexes(array());
        $this->setForeignKeys(array());
        $this->setData(array());
    }

    /**
     * Add a table column.
     *
     * Type can be: string, text, integer, float, decimal, datetime, timestamp,
     * time, date, binary, boolean.
     *
     * Valid options can be: limit, default, null, precision or scale.
     *
     * @param string|Column $columnName Column Name
     * @param string $type Column Type
     * @param array $options Column Options
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return Table
     */
    public function addColumn($columnName, $type = null, $options = array())
    {
        // we need an adapter set to add a column
        if (null === $this->getAdapter()) {
            throw new \RuntimeException('An adapter must be specified to add a column.');
        }

        // create a new column object if only strings were supplied
        if (!$columnName instanceof Column) {
            $column = new Column();
            $column->setName($columnName);
            $column->setType($type);
            $column->setOptions($options); // map options to column methods
        } else {
            $column = $columnName;
        }

        // Delegate to Adapters to check column type
        if (!$this->getAdapter()->isValidColumnType($column)) {
            throw new \InvalidArgumentException(sprintf(
                'An invalid column type "%s" was specified for column "%s".',
                $column->getType(),
                $column->getName()
            ));
        }

        $this->columns[] = $column;
        return $this;
    }

    /**
     * Remove a table column.
     *
     * @param string $columnName Column Name
     * @return Table
     */
    public function removeColumn($columnName)
    {
        $this->getAdapter()->dropColumn($this->getName(), $columnName);
        return $this;
    }

    /**
     * Rename a table column.
     *
     * @param string $oldName Old Column Name
     * @param string $newName New Column Name
     * @return Table
     */
    public function renameColumn($oldName, $newName)
    {
        $this->getAdapter()->renameColumn($this->getName(), $oldName, $newName);
        return $this;
    }

    /**
     * Change a table column type.
     *
     * @param string        $columnName    Column Name
     * @param string|Column $newColumnType New Column Type
     * @param array         $options       Options
     * @return Table
     */
    public function changeColumn($columnName, $newColumnType, $options = array())
    {
        // create a column object if one wasn't supplied
        if (!$newColumnType instanceof Column) {
            $newColumn = new Column();
            $newColumn->setType($newColumnType);
            $newColumn->setOptions($options);
        } else {
            $newColumn = $newColumnType;
        }

        // if the name was omitted use the existing column name
        if (null === $newColumn->getName() || strlen($newColumn->getName()) === 0) {
            $newColumn->setName($columnName);
        }

        $this->getAdapter()->changeColumn($this->getName(), $columnName, $newColumn);
        return $this;
    }

    /**
     * Checks to see if a column exists.
     *
     * @param string $columnName Column Name
     * @param array $options Options
     * @return boolean
     */
    public function hasColumn($columnName, $options = array())
    {
        return $this->getAdapter()->hasColumn($this->getName(), $columnName, $options);
    }

    /**
     * Add an index to a database table.
     *
     * In $options you can specific unique = true/false or name (index name).
     *
     * @param string|array|Index $columns Table Column(s)
     * @param array $options Index Options
     * @return Table
     */
    public function addIndex($columns, $options = array())
    {
        // create a new index object if strings or an array of strings were supplied
        if (!$columns instanceof Index) {
            $index = new Index();
            if (is_string($columns)) {
                $columns = array($columns); // str to array
            }
            $index->setColumns($columns);
            $index->setOptions($options);
        } else {
            $index = $columns;
        }

        $this->indexes[] = $index;
        return $this;
    }

    /**
     * Removes the given index from a table.
     *
     * @param array $columns Columns
     * @param array $options Options
     * @return Table
     */
    public function removeIndex($columns, $options = array())
    {
        $this->getAdapter()->dropIndex($this->getName(), $columns, $options);
        return $this;
    }

    /**
     * Removes the given index identified by its name from a table.
     *
     * @param string $name Index name
     * @return Table
     */
    public function removeIndexByName($name)
    {
        $this->getAdapter()->dropIndexByName($this->getName(), $name);
        return $this;
    }

    /**
     * Checks to see if an index exists.
     *
     * @param string|array $columns Columns
     * @param array        $options Options
     * @return boolean
     */
    public function hasIndex($columns, $options = array())
    {
        return $this->getAdapter()->hasIndex($this->getName(), $columns, $options);
    }

    /**
     * Add a foreign key to a database table.
     *
     * In $options you can specify on_delete|on_delete = cascade|no_action ..,
     * on_update, constraint = constraint name.
     *
     * @param string|array $columns Columns
     * @param string|Table $referencedTable   Referenced Table
     * @param string|array $referencedColumns Referenced Columns
     * @param array $options Options
     * @return Table
     */
    public function addForeignKey($columns, $referencedTable, $referencedColumns = array('id'), $options = array())
    {
        if (is_string($referencedColumns)) {
            $referencedColumns = array($referencedColumns); // str to array
        }
        $fk = new ForeignKey();
        if ($referencedTable instanceof Table) {
            $fk->setReferencedTable($referencedTable);
        } else {
            $fk->setReferencedTable(new Table($referencedTable, array(), $this->adapter));
        }
        $fk->setColumns($columns)
           ->setReferencedColumns($referencedColumns)
           ->setOptions($options);
        $this->foreignKeys[] = $fk;

        return $this;
    }

    /**
     * Removes the given foreign key from the table.
     *
     * @param string|array $columns    Column(s)
     * @param null|string  $constraint Constraint names
     * @return Table
     */
    public function dropForeignKey($columns, $constraint = null)
    {
        if (is_string($columns)) {
            $columns = array($columns);
        }
        if ($constraint) {
            $this->getAdapter()->dropForeignKey($this->getName(), array(), $constraint);
        } else {
            $this->getAdapter()->dropForeignKey($this->getName(), $columns);
        }

        return $this;
    }

    /**
     * Checks to see if a foreign key exists.
     *
     * @param  string|array $columns    Column(s)
     * @param  null|string  $constraint Constraint names
     * @return boolean
     */
    public function hasForeignKey($columns, $constraint = null)
    {
        return $this->getAdapter()->hasForeignKey($this->getName(), $columns, $constraint);
    }

    /**
     * Add timestamp columns created_at and updated_at to the table.
     *
     * @param string $createdAtColumnName
     * @param string $updatedAtColumnName
     *
     * @return Table
     */
    public function addTimestamps($createdAtColumnName = 'created_at', $updatedAtColumnName = 'updated_at')
    {
        $createdAtColumnName = is_null($createdAtColumnName) ? 'created_at' : $createdAtColumnName;
        $updatedAtColumnName = is_null($updatedAtColumnName) ? 'updated_at' : $updatedAtColumnName;
        $this->addColumn($createdAtColumnName, 'timestamp', array(
                'default' => 'CURRENT_TIMESTAMP',
                'update' => ''
            ))
             ->addColumn($updatedAtColumnName, 'timestamp', array(
                'null'    => true,
                'default' => null
             ));

        return $this;
    }

    /**
     * Insert data into the table.
     *
     * @param $data array of data in the form:
     *              array(
     *                  array("col1" => "value1", "col2" => "anotherValue1"),
     *                  array("col2" => "value2", "col2" => "anotherValue2"),
     *              )
     *              or array("col1" => "value1", "col2" => "anotherValue1")
     *
     * @return Table
     */
    public function insert($data)
    {
        // handle array of array situations
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $row) {
                $this->data[] = $row;
            }
            return $this;
        }
        $this->data[] = $data;
        return $this;
    }

    /**
     * Creates a table from the object instance.
     *
     * @return void
     */
    public function create()
    {
        $this->getAdapter()->createTable($this);
        $this->saveData();
        $this->reset(); // reset pending changes
    }

    /**
     * Updates a table from the object instance.
     *
     * @throws \RuntimeException
     * @return void
     */
    public function update()
    {
        if (!$this->exists()) {
            throw new \RuntimeException('Cannot update a table that doesn\'t exist!');
        }

        // update table
        foreach ($this->getPendingColumns() as $column) {
            $this->getAdapter()->addColumn($this, $column);
        }

        foreach ($this->getIndexes() as $index) {
            $this->getAdapter()->addIndex($this, $index);
        }

        foreach ($this->getForeignKeys() as $foreignKey) {
            $this->getAdapter()->addForeignKey($this, $foreignKey);
        }

        $this->saveData();
        $this->reset(); // reset pending changes
    }

    /**
     * Commit the pending data waiting for insertion.
     *
     * @return void
     */
    public function saveData()
    {
        foreach ($this->getData() as $row) {
            $this->getAdapter()->insert($this, $row);
        }
    }

    /**
     * Commits the table changes.
     *
     * If the table doesn't exist it is created otherwise it is updated.
     *
     * @return void
     */
    public function save()
    {
        if ($this->exists()) {
            $this->update(); // update the table
        } else {
            $this->create(); // create the table
        }

        $this->reset(); // reset pending changes
    }
}
