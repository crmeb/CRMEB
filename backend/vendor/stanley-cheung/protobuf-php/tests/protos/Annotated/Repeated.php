<?php

namespace Tests\Annotated;

class Repeated extends \DrSlump\Protobuf\AnnotatedMessage
{
    /** @protobuf(tag=1, type=string, repeated) */
    public $string;
    /** @protobuf(tag=2, type=int32, repeated) */
    public $int;
    /** @protobuf(tag=3, type=message, reference=tests\Annotated\RepeatedNested, repeated) */
    public $nested;
}

class RepeatedNested extends \DrSlump\Protobuf\AnnotatedMessage
{
    /** @protobuf(tag=1, type=int32) */
    public $id;
}
