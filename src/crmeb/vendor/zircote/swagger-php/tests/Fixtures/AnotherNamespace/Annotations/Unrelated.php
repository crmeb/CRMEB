<?php

namespace AnotherNamespace\Annotations;

/**
 * Unrelated annotation.
 *
 * @Annotation
 */
class Unrelated
{
    protected $s;

    public function __construct($s = null)
    {
        $this->s = $s;
    }
}