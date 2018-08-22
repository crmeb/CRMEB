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
 * @subpackage Phinx\Seed
 */
namespace Phinx\Seed;

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Table;
use think\console\Input as InputInterface;
use think\console\Output as OutputInterface;

/**
 * Seed interface
 *
 * @author Rob Morgan <robbym@gmail.com>
 */
interface SeedInterface
{
    /**
     * @var string
     */
    const RUN = 'run';

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run();

    /**
     * Sets the database adapter.
     *
     * @param AdapterInterface $adapter Database Adapter
     * @return MigrationInterface
     */
    public function setAdapter(AdapterInterface $adapter);

    /**
     * Gets the database adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter();

    /**
     * Sets the input object to be used in migration object
     *
     * @param InputInterface $input
     * @return MigrationInterface
     */
    public function setInput(InputInterface $input);

    /**
     * Gets the input object to be used in migration object
     *
     * @return InputInterface
     */
    public function getInput();

    /**
     * Sets the output object to be used in migration object
     *
     * @param OutputInterface $output
     * @return MigrationInterface
     */
    public function setOutput(OutputInterface $output);

    /**
     * Gets the output object to be used in migration object
     *
     * @return OutputInterface
     */
    public function getOutput();

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Executes a SQL statement and returns the number of affected rows.
     *
     * @param string $sql SQL
     * @return int
     */
    public function execute($sql);

    /**
     * Executes a SQL statement and returns the result as an array.
     *
     * @param string $sql SQL
     * @return array
     */
    public function query($sql);

    /**
     * Executes a query and returns only one row as an array.
     *
     * @param string $sql SQL
     * @return array
     */
    public function fetchRow($sql);

    /**
     * Executes a query and returns an array of rows.
     *
     * @param string $sql SQL
     * @return array
     */
    public function fetchAll($sql);

    /**
     * Insert data into a table.
     *
     * @param string $tableName
     * @param array $data
     * @return void
     */
    public function insert($tableName, $data);

    /**
     * Checks to see if a table exists.
     *
     * @param string $tableName Table Name
     * @return boolean
     */
    public function hasTable($tableName);

    /**
     * Returns an instance of the <code>\Table</code> class.
     *
     * You can use this class to create and manipulate tables.
     *
     * @param string $tableName Table Name
     * @param array $options Options
     * @return Table
     */
    public function table($tableName, $options);
}
