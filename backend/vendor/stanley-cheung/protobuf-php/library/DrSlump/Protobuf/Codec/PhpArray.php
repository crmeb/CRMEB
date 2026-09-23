<?php

namespace DrSlump\Protobuf\Codec;

use DrSlump\Protobuf;

/**
 * This codec serializes and unserializes data from/to PHP associative
 * arrays, allowing it to be used as a base for an arbitrary number
 * of different serializations (json, yaml, ini, xml ...).
 *
 */
class PhpArray implements Protobuf\CodecInterface
{
    /** @var bool */
    protected $useTagNumber = false;

    /**
     * Tells the codec to expect the array keys to contain the
     * field's tag number instead of the name.
     *
     * @param bool $useIt
     */
    public function useTagNumberAsKey($useIt = true)
    {
        $this->useTagNumber = $useIt;
    }

    /**
     * @param \DrSlump\Protobuf\Message $message
     * @return array
     */
    public function encode(Protobuf\Message $message)
    {
        return $this->encodeMessage($message);
    }

    /**
     * @param \DrSlump\Protobuf\Message $message
     * @param array $data
     * @return \DrSlump\Protobuf\Message
     */
    public function decode(Protobuf\Message $message, $data)
    {
        return $this->decodeMessage($message, $data);
    }

    protected function encodeMessage(Protobuf\Message $message)
    {
        $descriptor = Protobuf::getRegistry()->getDescriptor($message);

        $data = array();
        foreach ($descriptor->getFields() as $tag=>$field) {

            $empty = !$message->_has($tag);
            if ($field->isRequired() && $empty) {
                throw new \UnexpectedValueException(
                    'Message ' . get_class($message) . '\'s field tag ' . $tag . '(' . $field->getName() . ') is required but has no value'
                );
            }

            if ($empty) {
                continue;
            }

            $key = $this->useTagNumber ? $field->getNumber() : $field->getName();
            $v = $message->_get($tag);

            if ($field->isRepeated()) {
                // Make sure the value is an array of values
                $v = is_array($v) ? $v : array($v);
                foreach ($v as $k=>$vv) {
                    // Skip nullified repeated values
                    if (NULL === $vv) {
                        continue;
                    }
                    $v[$k] = $this->filterValue($vv, $field);
                }
            } else {
                $v = $this->filterValue($v, $field);
            }

            $data[$key] = $v;
        }

        return $data;
    }

    protected function decodeMessage(Protobuf\Message $message, $data)
    {
        // Get message descriptor
        $descriptor = Protobuf::getRegistry()->getDescriptor($message);

        foreach ($data as $key=>$v) {

            // Get the field by tag number or name
            $field = $this->useTagNumber
                   ? $descriptor->getField($key)
                   : $descriptor->getFieldByName($key);

            // Unknown field found
            if (!$field) {
                $unknown = new PhpArray\Unknown($key, gettype($v), $v);
                $message->addUnknown($unknown);
                continue;
            }

            if ($field->isRepeated()) {
                // Make sure the value is an array of values
                $v = is_array($v) && is_int(key($v)) ? $v : array($v);
                foreach ($v as $k=>$vv) {
                    $v[$k] = $this->filterValue($vv, $field);
                }
            } else {
                $v = $this->filterValue($v, $field);
            }

            $message->_set($field->getNumber(), $v);
        }

        return $message;
    }

    protected function filterValue($value, Protobuf\Field $field)
    {
        switch ($field->getType()) {
            case Protobuf::TYPE_MESSAGE:
                // Tell apart encoding and decoding
                if ($value instanceof Protobuf\Message) {
                    return $this->encodeMessage($value);
                } else {
                    $nested = $field->getReference();
                    return $this->decodeMessage(new $nested, $value);
                }
            case Protobuf::TYPE_BOOL:
                if ($value === true || $value === false) {
                    return $value;
                }
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case Protobuf::TYPE_STRING:
            case Protobuf::TYPE_BYTES:
                return (string)$value;
            case Protobuf::TYPE_FLOAT:
            case Protobuf::TYPE_DOUBLE:
                return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            // Assume the rest are ints
            default:
                return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        }
    }


}
