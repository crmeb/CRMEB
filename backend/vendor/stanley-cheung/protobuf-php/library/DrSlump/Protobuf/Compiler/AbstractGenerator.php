<?php

namespace DrSlump\Protobuf\Compiler;

use google\protobuf as proto;

abstract class AbstractGenerator
{
    /** @var \DrSlump\Protobuf\Compiler */
    protected $compiler;
    /** @var \google\protobuf\FileDescriptorProto */
    protected $proto;

    /** @var array */
    protected $extensions = array();

    public function __construct(\DrSlump\Protobuf\Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    public function getNamespace(proto\FileDescriptorProto $proto = NULL)
    {
        return NULL === $proto
               ? $this->proto->getPackage()
               : $proto->getPackage();
    }

    public function generate(proto\FileDescriptorProto $proto)
    {
        $this->proto = $proto;
    }


    abstract protected function compileEnum(proto\EnumDescriptorProto $enum, $namespace);

    abstract protected function compileMessage(proto\DescriptorProto $msg, $namespace);

    abstract protected function compileExtension(proto\FieldDescriptorProto $field, $ns, $indent);
}
