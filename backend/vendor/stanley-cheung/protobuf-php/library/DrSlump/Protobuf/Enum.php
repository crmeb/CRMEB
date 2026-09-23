<?php

namespace DrSlump\Protobuf;

use DrSlump\Protobuf;


abstract class Enum extends \IteratorIterator implements \ArrayAccess
{
    /** @var array */
    protected $constants = array();


    /**
     * @static
     * @return \DrSlump\Protobuf\Enum
     */
    public static function getInstance()
    {
        static $instances = array();

        $class = get_called_class();

        if (!isset($instances[$class])) {
            $instances[$class] = new static();
        }

        return $instances[$class];
    }

    /**
     */
    public function __construct()
    {
        $refl = new \ReflectionObject($this);
        $this->constants = $refl->getConstants();

        parent::__construct(new \ArrayIterator($this->constants));
    }


    /**
     * Helper method to obtain the constants as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->constants;
    }


    // Implements ArrayAccess for extensions and unknown fields

    public function offsetExists($offset)
    {
        if (is_int($offset)) {
            return FALSE !== array_search($offset, $this->constants);
        } else {
            return isset($this->constants[$offset]);
        }
    }

    public function offsetGet( $offset )
    {
        if (is_int($offset)) {
            return array_search($offset, $this->constants) ?: NULL;
        } else {
            return isset($this->constants[$offset]) ? $this->constants[$offset] : NULL;
        }
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Enum cannot be modified');
    }

    public function offsetUnset( $offset )
    {
        throw new \RuntimeException('Enum cannot be modified');
    }


    // Implements magic getter

    public function __isset($name)
    {
        return isset($this->constants[$name]);
    }

    public function __get($name)
    {
        return isset($this->constants[$name])
               ? $this->constants[$name]
               : NULL;
    }
}
