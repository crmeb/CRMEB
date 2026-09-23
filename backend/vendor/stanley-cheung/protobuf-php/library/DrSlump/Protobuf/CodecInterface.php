<?php

namespace DrSlump\Protobuf;

interface CodecInterface
{
    public function encode(\DrSlump\Protobuf\Message $message);
    public function decode(\DrSlump\Protobuf\Message $message, $data);
}