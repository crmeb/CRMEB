<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Util;

class Expression
{
    /**
     * @var string The expression
     */
    protected $value;

    /**
     * @param string $value The expression
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string Returns the expression
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param string $value The expression
     * @return self
     */
    public static function from(string $value): Expression
    {
        return new self($value);
    }
}
