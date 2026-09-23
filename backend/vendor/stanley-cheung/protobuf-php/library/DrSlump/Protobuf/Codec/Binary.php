<?php

namespace DrSlump\Protobuf\Codec;

use DrSlump\Protobuf;

class Binary implements Protobuf\CodecInterface
{
    const WIRE_VARINT      = 0;
    const WIRE_FIXED64     = 1;
    const WIRE_LENGTH      = 2;
    const WIRE_GROUP_START = 3;
    const WIRE_GROUP_END   = 4;
    const WIRE_FIXED32     = 5;
    const WIRE_UNKNOWN     = -1;

    // Table map to know if a field type is "packable"
    static $PACKABLE = array(
        Protobuf::TYPE_DOUBLE => true,
        Protobuf::TYPE_FLOAT => true,
        Protobuf::TYPE_INT64 => true,
        Protobuf::TYPE_UINT64 => true,
        Protobuf::TYPE_INT32 => true,
        Protobuf::TYPE_FIXED64 => true,
        Protobuf::TYPE_FIXED32 => true,
        Protobuf::TYPE_BOOL => true,
        Protobuf::TYPE_STRING => false,
        Protobuf::TYPE_GROUP => false,
        Protobuf::TYPE_MESSAGE => false,
        Protobuf::TYPE_BYTES => false,
        Protobuf::TYPE_UINT32 => true,
        Protobuf::TYPE_ENUM => true,
        Protobuf::TYPE_SFIXED32 => true,
        Protobuf::TYPE_SFIXED64 => true,
        Protobuf::TYPE_SINT32 => true,
        Protobuf::TYPE_SINT64 => true
    );

    /**
     * @param \DrSlump\Protobuf\Message $message
     * @return string
     */
    public function encode(Protobuf\Message $message)
    {
        return $this->encodeMessage($message);
    }

    /**
     * @param String|Message $message
     * @param String $data
     * @return \DrSlump\Protobuf\Message
     */
    public function decode(Protobuf\Message $message, $data)
    {
        static $reader;

        // Create a single reader for all messages to be parsed
        if (!$reader) {
            $reader = new Protobuf\Codec\Binary\Reader();
        }

        // Initialize the reader with the current message data
        $reader->init($data);

        return $this->decodeMessage($reader, $message);
    }


    protected function encodeMessage(Protobuf\Message $message)
    {
        $writer = new Binary\Writer();

        // Get message descriptor
        $descriptor = Protobuf::getRegistry()->getDescriptor($message);

        foreach ($descriptor->getFields() as $tag=>$field) {

            $empty = !$message->_has($tag);
            if ($field->isRequired() && $empty) {
                throw new \UnexpectedValueException(
                    'Message ' . get_class($message) . '\'s field tag ' . $tag . '(' . $field->getName() . ') is required but has no value'
                );
            }

            // Skip empty fields
            if ($empty) {
                continue;
            }

            $type = $field->getType();
            $wire = $field->isPacked() ? self::WIRE_LENGTH : $this->getWireType($type, null);

            // Compute key with tag number and wire type
            $key = $tag << 3 | $wire;

            $value = $message->_get($tag);

            if ($field->isRepeated()) {

                // Packed fields are encoded as a length-delimited stream containing
                // the concatenated encoding of each value.
                if ($field->isPacked() && !empty($value)) {
                    $subwriter = new Binary\Writer();
                    foreach($value as $val) {
                        $this->encodeSimpleType($subwriter, $type, $val);
                    }
                    $data = $subwriter->getBytes();
                    $writer->varint($key);
                    $writer->varint(strlen($data));
                    $writer->write($data);
                } else {
                    
                    // Make sure the value is an array of values
                    $value = is_array($value) ? $value : array($value);
                    foreach($value as $val) {
                        // Skip nullified repeated values
                        if (NULL === $val) {
                            continue;
                        } else if ($type !== Protobuf::TYPE_MESSAGE) {
                            $writer->varint($key);
                            $this->encodeSimpleType($writer, $type, $val);
                        } else {
                            $writer->varint($key);
                            $data = $this->encodeMessage($val);
                            $writer->varint(strlen($data));
                            $writer->write($data);
                        }
                    }
                }
            } else if ($type !== Protobuf::TYPE_MESSAGE) {
                $writer->varint($key);
                $this->encodeSimpleType($writer, $type, $value);
            } else {
                $writer->varint($key);
                $data = $this->encodeMessage($value);
                $writer->varint(strlen($data));
                $writer->write($data);
            }
        }

        return $writer->getBytes();
    }

    protected function encodeSimpleType($writer, $type, $value)
    {
        switch ($type) {
            case Protobuf::TYPE_INT32:
            case Protobuf::TYPE_INT64:
            case Protobuf::TYPE_UINT64:
            case Protobuf::TYPE_UINT32:
                $writer->varint($value);
                break;

            case Protobuf::TYPE_SINT32: // ZigZag
                $writer->zigzag($value, 32);
                break;

            case Protobuf::TYPE_SINT64 : // ZigZag
                $writer->zigzag($value, 64);
                break;

            case Protobuf::TYPE_DOUBLE:
                $writer->double($value);
                break;
            case Protobuf::TYPE_FIXED64:
                $writer->fixed64($value);
                break;
            case Protobuf::TYPE_SFIXED64:
                $writer->sFixed64($value);
                break;

            case Protobuf::TYPE_FLOAT:
                $writer->float($value);
                break;
            case Protobuf::TYPE_FIXED32:
                $writer->fixed32($value);
                break;
            case Protobuf::TYPE_SFIXED32:
                $writer->sFixed32($value);
                break;

            case Protobuf::TYPE_BOOL:
                $writer->varint($value ? 1 : 0);
                break;

            case Protobuf::TYPE_STRING:
            case Protobuf::TYPE_BYTES:
                $writer->varint(strlen($value));
                $writer->write($value);
                break;

            case Protobuf::TYPE_MESSAGE:
                // Messages are not supported in this method
                return null;

            case Protobuf::TYPE_ENUM:
                $writer->varint($value);
                break;

            default:
                throw new \Exception('Unknown field type ' . $type);
        }
    }

    /**
     * @param \DrSlump\Protobuf\Codec\Binary\Reader $reader
     * @param \DrSlump\Protobuf\Message             $message
     * @param int                                   $length 
     * @return \DrSlump\Protobuf\Message
     */
    protected function decodeMessage($reader, \DrSlump\Protobuf\Message $message, $length = NULL)
    {
        /** @var $message \DrSlump\Protobuf\Message */
        /** @var $descriptor \DrSlump\Protobuf\Descriptor */

        // Get message descriptor
        $descriptor = Protobuf::getRegistry()->getDescriptor($message);
        // Cache locally the message fields
        $fields = $descriptor->getFields();

        // Calculate the maximum offset if we have defined a length
        $limit = !is_null($length) ? $reader->pos() + $length : NULL;
        $pos = $reader->pos();

        // Keep reading until we reach the end or the limit
        while ($limit === NULL && !$reader->eof() || $limit !== NULL && $reader->pos() < $limit) {

            // Get initial varint with tag number and wire type
            $key = $reader->varint();
            if ($reader->eof()) break;

            $wire = $key & 0x7;
            $tag = $key >> 3;

            // Find the matching field for the tag number
            if (!isset($fields[$tag])) {
                $data = $this->decodeUnknown($reader, $wire);
                $unknown = new Binary\Unknown($tag, $wire, $data);
                $message->addUnknown($unknown);
                continue;
            }

            $field = $fields[$tag];
            $type = $field->getType();

            // Check if we are dealing with a packed stream, we cannot rely on the packed
            // flag of the message since we cannot be certain if the creator of the message
            // was using it.
            if ($wire === self::WIRE_LENGTH && $field->isRepeated() && self::$PACKABLE[$type]) {
                $len = $reader->varint();
                $until = $reader->pos() + $len;
                $wire = $this->getWireType($type);
                while ($reader->pos() < $until) {
                    $item = $this->decodeSimpleType($reader, $type, $wire);
                    $message->_add($tag, $item);
                }

            } else {

                // Assert wire and type match
                $this->assertWireType($wire, $type);

                // Check if it's a sub-message
                if ($type === Protobuf::TYPE_MESSAGE) {
                    $submessage = $field->getReference();
                    $submessage = new $submessage;

                    $len = $reader->varint();
                    $value = $this->decodeMessage($reader, $submessage, $len);
                } else {
                    $value = $this->decodeSimpleType($reader, $type, $wire);
                }

                // Support non-packed repeated fields
                if ($field->isRepeated()) {
                    $message->_add($tag, $value);
                } else {
                    $message->_set($tag, $value);
                }
            }
        }

        return $message;
    }

    protected function isPackable($type)
    {
        static $packable = array(
            Protobuf::TYPE_INT64,
            Protobuf::TYPE_UINT64,
            Protobuf::TYPE_INT32,
            Protobuf::TYPE_UINT32,
            Protobuf::TYPE_SINT32,
            Protobuf::TYPE_SINT64,
            Protobuf::TYPE_DOUBLE,
            Protobuf::TYPE_FIXED64,
            Protobuf::TYPE_SFIXED64,
            Protobuf::TYPE_FLOAT,
            Protobuf::TYPE_FIXED32,
            Protobuf::TYPE_SFIXED32,
            Protobuf::TYPE_BOOL,
            Protobuf::TYPE_ENUM
        );

        return in_array($type, $packable);
    }

    protected function decodeUnknown($reader, $wire)
    {
        switch ($wire) {
            case self::WIRE_VARINT:
                return $reader->varint();
            case self::WIRE_LENGTH:
                $length = $reader->varint();
                return $reader->read($length);
            case self::WIRE_FIXED32:
                return $reader->fixed32();
            case self::WIRE_FIXED64:
                return $reader->fixed64();
            case self::WIRE_GROUP_START:
            case self::WIRE_GROUP_END:
                throw new \RuntimeException('Groups are deprecated in Protocol Buffers and unsupported by this library');
            default:
                throw new \RuntimeException('Unsupported wire type (' . $wire . ') while consuming unknown field');
        }
    }

    protected function assertWireType($wire, $type)
    {
        $expected = $this->getWireType($type, $wire);
        if ($wire !== $expected) {
            throw new \RuntimeException("Expected wire type $expected but got $wire for type $type");
        }
    }

    protected function getWireType($type, $default = null)
    {
        static $map = array(
            Protobuf::TYPE_INT32 => self::WIRE_VARINT,
            Protobuf::TYPE_INT64 => self::WIRE_VARINT,
            Protobuf::TYPE_UINT32 => self::WIRE_VARINT,
            Protobuf::TYPE_UINT64 => self::WIRE_VARINT,
            Protobuf::TYPE_SINT32 => self::WIRE_VARINT,
            Protobuf::TYPE_SINT64 => self::WIRE_VARINT,
            Protobuf::TYPE_BOOL => self::WIRE_VARINT,
            Protobuf::TYPE_ENUM => self::WIRE_VARINT,
            Protobuf::TYPE_FIXED64 => self::WIRE_FIXED64,
            Protobuf::TYPE_SFIXED64 => self::WIRE_FIXED64,
            Protobuf::TYPE_DOUBLE => self::WIRE_FIXED64,
            Protobuf::TYPE_STRING => self::WIRE_LENGTH,
            Protobuf::TYPE_BYTES => self::WIRE_LENGTH,
            Protobuf::TYPE_MESSAGE => self::WIRE_LENGTH,
            Protobuf::TYPE_FIXED32 => self::WIRE_FIXED32,
            Protobuf::TYPE_SFIXED32 => self::WIRE_FIXED32,
            Protobuf::TYPE_FLOAT => self::WIRE_FIXED32
        );

        // Unknown types just return the reported wire type
        return isset($map[$type]) ? $map[$type] : $default;
    }

    protected function decodeSimpleType($reader, $type, $wireType)
    {
        switch ($type) {
            case Protobuf::TYPE_INT64:
            case Protobuf::TYPE_UINT64:
            case Protobuf::TYPE_INT32:
            case Protobuf::TYPE_UINT32:
            case Protobuf::TYPE_ENUM:
                return $reader->varint();

            case Protobuf::TYPE_SINT32: // ZigZag
                return $reader->zigzag();
            case Protobuf::TYPE_SINT64: // ZigZag
                return $reader->zigzag();
            case Protobuf::TYPE_DOUBLE:
                return $reader->double();
            case Protobuf::TYPE_FIXED64:
                return $reader->fixed64();
            case Protobuf::TYPE_SFIXED64:
                return $reader->sFixed64();

            case Protobuf::TYPE_FLOAT:
                return $reader->float();
            case Protobuf::TYPE_FIXED32:
                return $reader->fixed32();
            case Protobuf::TYPE_SFIXED32:
                return $reader->sFixed32();

            case Protobuf::TYPE_BOOL:
                return (bool)$reader->varint();

            case Protobuf::TYPE_STRING:
            case Protobuf::TYPE_BYTES:
                $length = $reader->varint();
                return $reader->read($length);

            case Protobuf::TYPE_MESSAGE:
                throw new \RuntimeException('Nested messages are not supported in this method');

            default:
                // Unknown type, follow wire type rules
                switch ($wireType) {
                    case self::WIRE_VARINT:
                        return $reader->varint();
                    case self::WIRE_FIXED32:
                        return $reader->fixed32();
                    case self::WIRE_FIXED64:
                        return $reader->fixed64();
                    case self::WIRE_LENGTH:
                        $length = $reader->varint();
                        return $reader->read($length);
                    case self::WIRE_GROUP_START:
                    case self::WIRE_GROUP_END:
                        throw new \RuntimeException('Group is deprecated and not supported');
                    default:
                        throw new \RuntimeException('Unsupported wire type number ' . $wireType);
                }
        }

    }

}
