<?php

namespace DrSlump\Protobuf\Codec\Binary;

class Unknown extends \DrSlump\Protobuf\Unknown
{
    public function __construct($tag, $type, $data)
    {
        $this->tag = $tag;
        $this->type = $type;
        $this->data = $data;
    }
}
