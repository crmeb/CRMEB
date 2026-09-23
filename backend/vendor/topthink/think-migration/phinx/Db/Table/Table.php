<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Table;

use InvalidArgumentException;

class Table
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string, mixed>
     */
    protected $options;

    /**
     * @param string $name The table name
     * @param array<string, mixed> $options The creation options for this table
     * @throws \InvalidArgumentException
     */
    public function __construct($name, array $options = [])
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Cannot use an empty table name');
        }

        $this->name = $name;
        $this->options = $options;
    }

    /**
     * Sets the table name.
     *
     * @param string $name The name of the table
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the table name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the table options
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets the table options
     *
     * @param array<string, mixed> $options The options for the table creation
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
