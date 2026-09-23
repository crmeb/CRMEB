<?php

namespace DrSlump\Protobuf;

use DrSlump\Protobuf;

abstract class AnnotatedMessage extends Message
{
    static public function descriptor()
    {
        $class = get_called_class();

        // Instantiate a new descriptor
        $descriptor = new Descriptor($class);

        $rflClass = new \ReflectionClass($class);
        $props = $rflClass->getProperties();
        foreach ($props as $prop) {
            $doc = $prop->getDocComment();
            if (empty($doc)) {
                continue;
            }

            // Format: @protobuf(tag=X, type=bool, required=true)

            // Extract annotation from the comment
            if (!preg_match('/@protobuf\s?\(([^\)]+)\)/', $doc, $m)) {
                continue;
            }

            // Parse params
            $params = explode(',', $m[1]);
            $params = array_filter(array_map('trim', $params));

            $options = array();
            foreach ($params as $param) {
                $parts = explode('=', $param);
                $parts = array_filter(array_map('trim', $parts));
                $options[$parts[0]] = count($parts) < 2
                                    ? true
                                    : $parts[1];
            }

            // Check if we have the minimum required options
            if (empty($options['tag'])) {
                throw new \InvalidArgumentException('The tag option is required for property ' . $prop->getName());
            }
            if (empty($options['type'])) {
                throw new \InvalidArgumentException('The type option is required for property ' . $prop->getName());
            }

            // Normalize boolean values
            foreach(array('required', 'optional', 'repeated', 'packed') as $opt) {
                if (isset($options[$opt])) {
                    $options[$opt] = filter_var($options[$opt], FILTER_VALIDATE_BOOLEAN);
                }
            }

            // Build a field descriptor
            $f = new Protobuf\Field();
            $f->number    = (int)$options['tag'];
            $f->name      = $prop->getName();

            // Convert type name to its numeric constant
            switch (strtolower($options['type'])) {
            case 'double'  : $f->type = Protobuf::TYPE_DOUBLE; break;
            case 'float'   : $f->type = Protobuf::TYPE_FLOAT; break;
            case 'int64'   : $f->type = Protobuf::TYPE_INT64; break;
            case 'uint64'  : $f->type = Protobuf::TYPE_UINT64; break;
            case 'int32'   : $f->type = Protobuf::TYPE_INT32; break;
            case 'fixed64' : $f->type = Protobuf::TYPE_FIXED64; break;
            case 'fixed32' : $f->type = Protobuf::TYPE_FIXED32; break;
            case 'bool'    : $f->type = Protobuf::TYPE_BOOL; break;
            case 'string'  : $f->type = Protobuf::TYPE_STRING; break;
            case 'message' : $f->type = Protobuf::TYPE_MESSAGE; break;
            case 'bytes'   : $f->type = Protobuf::TYPE_BYTES; break;
            case 'uint32'  : $f->type = Protobuf::TYPE_UINT32; break;
            case 'enum'    : $f->type = Protobuf::TYPE_ENUM; break;
            case 'sfixed32': $f->type = Protobuf::TYPE_SFIXED32; break;
            case 'sfixed64': $f->type = Protobuf::TYPE_SFIXED64; break;
            case 'sint32'  : $f->type = Protobuf::TYPE_SINT32; break;
            case 'sint64'  : $f->type = Protobuf::TYPE_SINT64; break;
            default:
                throw new \InvalidArgumentException('Type ' . $options['type'] . ' is not recognized as valid for property ' . $prop->getName());
            }

            // Define the rule type
            $f->rule = Protobuf::RULE_OPTIONAL;
            if (!empty($options['required'])) $f->rule = Protobuf::RULE_REQUIRED;
            if (!empty($options['repeated'])) $f->rule = Protobuf::RULE_REPEATED;

            // Check if it's flagged as packed
            if (isset($options['packed'])) {
                $f->packed = $options['packed'];
            }

            // Get the reference
            if (isset($options['reference'])) {
                $f->reference = $options['reference'];
            } else if ($f->type === Protobuf::TYPE_MESSAGE || $f->type === Protobuf::TYPE_ENUM) {
                throw new \InvalidArgumentException('Property ' . $prop->getName() . ' requires the "reference" option');
            }

            if (isset($options['default'])) {
                $f->default = $options['default'];
            }

            // Add the field to the message descriptor
            $descriptor->addField($f);
        }

        return $descriptor;
    }

}
