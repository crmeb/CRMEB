<?php

namespace DrSlump\Protobuf;

use DrSlump\Protobuf;

abstract class Message implements \ArrayAccess
{
    /** @var \Closure[] */
    static protected $__extensions = array();

    /** @var \DrSlump\Protobuf\Descriptor */
    protected $_descriptor;
    /** @var array Store data for extension fields */
    protected $_extensions = array();
    /** @var array Store data for unknown values */
    protected $_unknown = array();

    /**
     * @static
     * @abstract
     * @return \DrSlump\Protobuf\Descriptor
     */
    public static function descriptor()
    {
        throw new \BadMethodCallException('This method should be implemented in inherited classes');
    }

    /**
     * Register an extension configuration callback
     *
     * @static
     * @param \Closure $fn
     */
    public static function extension(\Closure $fn)
    {
        static::$__extensions[] = $fn;
    }

    /**
     * @param string $data
     */
    public function __construct($data = null)
    {
        // Cache the descriptor instance
        $this->_descriptor = Protobuf::getRegistry()->getDescriptor($this);

        // Assign default values to extensions
        foreach ($this->_descriptor->getFields() as $f) {
           if ($f->isExtension() && $f->hasDefault()) {
               $this->_extensions[$f->getName()] = $f->getDefault();
           }
        }

        if (NULL !== $data) {
            $this->parse($data);
        }
    }

    // Implements ArrayAccess for extensions and unknown fields

    public function offsetExists($offset)
    {
        if (is_numeric($offset)) {
            return $this->_has($offset);
        } else {
            return $this->_hasExtension($offset);
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_numeric($offset)) {
            $this->_set($offset, $value);
        } else {
            $this->_setExtension($offset, $value);
        }
    }

    public function offsetGet( $offset )
    {
        if (is_numeric($offset)) {
            return $this->_get($offset);
        } else {
            return $this->_getExtension($offset);
        }
    }

    public function offsetUnset( $offset )
    {
        if (is_numeric($offset)) {
            $this->_clear($offset);
        } else {
            $this->_clearExtension($offset);
        }
    }

    /**
     * Parse the given data to hydrate the object
     *
     * @param string $data
     * @param CodecInterface|null $codec
     */
    public function parse($data, Protobuf\CodecInterface $codec = null)
    {
        $codec = Protobuf::getCodec($codec);
        $codec->decode($this, $data);
    }

    public static function deserialize($data,
                                       Protobuf\CodecInterface $codec = null){
      $retval = new static();
      $retval->parse($data, $codec);
      return $retval;
    }

    /**
     * Serialize the current object data
     *
     * @param CodecInterface|null $codec
     * @return string
     */
    public function serialize(Protobuf\CodecInterface $codec = null)
    {
        $codec = Protobuf::getCodec($codec);
        return $codec->encode($this);
    }


    /**
     * Checks if the given tag number is set
     *
     * @param int $tag
     * @return bool
     */
    public function _has($tag)
    {
        if ($this->_descriptor->hasField($tag)) {
            $f = $this->_descriptor->getField($tag);
            $name = $f->getName();

            if ($f->isExtension()) {
                return $f->isRepeated()
                       ? count($this->_extensions[$name]) > 0
                       : $this->_extensions[$name] !== NULL;
            } else {
                return $f->isRepeated()
                       ? count($this->$name) > 0
                       : $this->$name !== NULL;
            }
        }

        return false;
    }

    /**
     * Get the value by tag number
     *
     * @param int $tag
     * @param int|null $idx
     * @return mixed
     */
    public function _get($tag, $idx = null)
    {
        $f = $this->_descriptor->getField($tag);

        if (!$f) {
            return null;
        }

        $name = $f->getName();

        if (!$f->isExtension()) {

            return $idx !== NULL
                   ? $this->{$name}[$idx]
                   : $this->$name;

        } else {

            return $idx !== NULL
                   ? $this->_extensions[$name][$idx]
                   : $this->_extensions[$name];

        }

    }

    /**
     * Sets the value by tag number
     *
     * @throws \Exception If trying to set an unknown field
     * @param int $tag
     * @param mixed $value
     * @param int|null $idx
     * @return \DrSlump\Protobuf\Message - Fluent interface
     */
    public function _set($tag, $value, $idx = null)
    {
        $f = $this->_descriptor->getField($tag);

        if (!$f) {
            throw new \Exception('Unknown fields not supported');
        }

        $name = $f->getName();
        if (!$f->isExtension()) {

            if ($idx === NULL) {
                $this->$name = $value;
            } else {
                $this->{$name}[$idx] = $value;
            }

        } else {
            if ($idx === NULL) {
                $this->_extensions[$name] = $value;
            } else {
                $this->_extensions[$name][$idx] = $value;
            }
        }

        return $this;
    }

    /**
     * Adds a new value to a repeated field by tag number
     *
     * @throws \Exception If trying to modify an unknown field
     * @param int $tag
     * @param mixed $value
     * @return Message
     */
    public function _add($tag, $value)
    {
        $f = $this->_descriptor->getField($tag);

        if (!$f) {
            throw new \Exception('Unknown fields not supported');
        }

        $name = $f->getName();
        if (!$f->isExtension()) {
            $this->{$name}[] = $value;
        } else {
            $this->_extensions[$name][] = $value;
        }

        return $this;
    }

    /**
     * Clears/Resets a field by tag number
     *
     * @throws \Exception If trying to modify an unknown field
     * @param int $tag
     * @return \DrSlump\Protobuf\Message - Fluent interface
     */
    public function _clear($tag)
    {
        $f = $this->_descriptor->getField($tag);

        if (!$f) {
            throw new \Exception('Unknown fields not supported');
        }

        $name = $f->getName();
        if (!$f->isExtension()) {
            $this->$name = $f->isRepeated()
                           ? array()
                           : NULL;
        } else {
            $this->_extensions[$name] = $f->isRepeated()
                                      ? array()
                                      : NULL;
        }

        return $this;
    }

    // Extensions public methods.
    // @todo Check if extension name is defined

    /**
     * Checks if an extension field is set
     *
     * @param string $extname
     * @return bool
     */
    public function _hasExtension($extname)
    {
        return isset($this->_extensions[$extname]);
    }

    /**
     * Get the value of an extension field
     *
     * @param string $extname
     * @param int|null $idx
     * @return mixed
     */
    public function _getExtension($extname, $idx = null)
    {
        if (!isset($this->_extensions[$extname])) return NULL;

        return $idx === NULL
               ? $this->_extensions[$extname]
               : $this->_extensions[$extname][$idx];
    }

    /**
     * Get all the values of a repeated extension field
     *
     * @param string $extname
     * @return array
     */
    public function _getExtensionList($extname)
    {
        return isset($this->_extensions[$extname])
               ? $this->_extensions[$extname]
               : array();
    }

    /**
     * Set the value for an extension field
     *
     * @param string $extname
     * @param mixed $value
     * @param int|null $idx
     * @return \DrSlump\Protobuf\Message - Fluent Interface
     */
    public function _setExtension($extname, $value, $idx = null)
    {
        if (NULL !== $idx) {
            if (empty($this->_extensions)) {
                $this->_extensions[$extname] = array();
            }
            $this->_extensions[$extname][$idx] = $value;
        }

        $this->_extensions[$extname] = $value;

        return $this;
    }

    /**
     * Adds a value to repeated extension field
     *
     * @param string $extname
     * @param mixed $value
     * @return \DrSlump\Protobuf\Message - Fluent Interface
     */
    public function _addExtension($extname, $value)
    {
        $this->_extensions[$extname][] = $value;
    }

    /**
     * @param  $extname
     * @return void
     */
    public function _clearExtension($extname)
    {
        unset($this->_extensions[$extname]);
    }


    public function addUnknown(Unknown $unknown)
    {
        $this->_unknown[] = $unknown;
    }

    public function getUnknown()
    {
        return $this->_unknown;
    }

    public function clearUnknown()
    {
        $this->_unknown = array();
    }

}
