<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Util;

/**
 * Contains all the information for running an ALTER command for a table,
 * and any post-steps required after the fact.
 */
class AlterInstructions
{
    /**
     * @var string[] The SQL snippets to be added to an ALTER instruction
     */
    protected $alterParts = [];

    /**
     * @var (string|callable)[] The SQL commands to be executed after the ALTER instruction
     */
    protected $postSteps = [];

    /**
     * Constructor
     *
     * @param string[] $alterParts SQL snippets to be added to a single ALTER instruction per table
     * @param (string|callable)[] $postSteps SQL commands to be executed after the ALTER instruction
     */
    public function __construct(array $alterParts = [], array $postSteps = [])
    {
        $this->alterParts = $alterParts;
        $this->postSteps = $postSteps;
    }

    /**
     * Adds another part to the single ALTER instruction
     *
     * @param string $part The SQL snipped to add as part of the ALTER instruction
     * @return void
     */
    public function addAlter(string $part): void
    {
        $this->alterParts[] = $part;
    }

    /**
     * Adds a SQL command to be executed after the ALTER instruction.
     * This method allows a callable, with will get an empty array as state
     * for the first time and will pass the return value of the callable to
     * the next callable, if present.
     *
     * This allows to keep a single state across callbacks.
     *
     * @param string|callable $sql The SQL to run after, or a callable to execute
     * @return void
     */
    public function addPostStep($sql): void
    {
        $this->postSteps[] = $sql;
    }

    /**
     * Returns the alter SQL snippets
     *
     * @return string[]
     */
    public function getAlterParts(): array
    {
        return $this->alterParts;
    }

    /**
     * Returns the SQL commands to run after the ALTER instruction
     *
     * @return (string|callable)[]
     */
    public function getPostSteps(): array
    {
        return $this->postSteps;
    }

    /**
     * Merges another AlterInstructions object to this one
     *
     * @param \Phinx\Db\Util\AlterInstructions $other The other collection of instructions to merge in
     * @return void
     */
    public function merge(AlterInstructions $other): void
    {
        $this->alterParts = array_merge($this->alterParts, $other->getAlterParts());
        $this->postSteps = array_merge($this->postSteps, $other->getPostSteps());
    }

    /**
     * Executes the ALTER instruction and all of the post steps.
     *
     * @param string $alterTemplate The template for the alter instruction
     * @param callable $executor The function to be used to execute all instructions
     * @return void
     */
    public function execute(string $alterTemplate, callable $executor): void
    {
        if ($this->alterParts) {
            $alter = sprintf($alterTemplate, implode(', ', $this->alterParts));
            $executor($alter);
        }

        $state = [];

        foreach ($this->postSteps as $instruction) {
            if (is_callable($instruction)) {
                $state = $instruction($state);
                continue;
            }

            $executor($instruction);
        }
    }
}
