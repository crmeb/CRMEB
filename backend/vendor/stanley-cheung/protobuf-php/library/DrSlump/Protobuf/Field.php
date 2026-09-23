<?php

namespace DrSlump\Protobuf;

use DrSlump\Protobuf;

class Field
{
    public $number;
    public $name;
    public $type = Protobuf::TYPE_UNKNOWN;
    public $rule = Protobuf::RULE_OPTIONAL;
    public $reference;
    public $default;
    public $packed = false;
    public $extension = false;

    public function __construct(array $opts = array())
    {
        if (!empty($opts)) {
            if (isset($opts['number'])) $this->number = (int)$opts['number'];
            if (isset($opts['name'])) $this->name = $opts['name'];
            if (isset($opts['type'])) $this->type = (int)$opts['type'];
            if (isset($opts['rule'])) $this->rule = (int)$opts['rule'];
            if (isset($opts['packed'])) $this->packed = (bool)$opts['packed'];
            if (isset($opts['reference'])) $this->reference = $opts['reference'];
            if (isset($opts['default'])) $this->default = $opts['default'];
            if (isset($opts['extension'])) $this->extension = (bool)$opts['extension'];
        }
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function hasDefault()
    {
        return $this->default !== NULL;
    }

    public function isOptional()
    {
        return $this->rule === Protobuf::RULE_OPTIONAL;
    }

    public function isRequired()
    {
        return $this->rule === Protobuf::RULE_REQUIRED;
    }

    public function isRepeated()
    {
        return $this->rule === Protobuf::RULE_REPEATED;
    }

    public function isPacked()
    {
        return $this->packed;
    }

    public function isExtension()
    {
        return $this->extension;
    }
    
    public function isMessage()
    {
        return $this->type === Protobuf::TYPE_MESSAGE;
    }
}