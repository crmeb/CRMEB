<?php

namespace Tests\Annotated;

class Simple extends \DrSlump\Protobuf\AnnotatedMessage
{
    /** @protobuf(tag=1, type=string, required) */
    public $foo;
    /** @protobuf(tag=2, type=int32, required) */
    public $bar;
    /** @protobuf(tag=3, type=string, optional) */
    public $baz;
}
