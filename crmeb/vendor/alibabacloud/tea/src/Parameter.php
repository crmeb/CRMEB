<?php

namespace AlibabaCloud\Tea;

use ArrayIterator;
use IteratorAggregate;
use ReflectionObject;
use Traversable;

/**
 * Class Parameter.
 */
abstract class Parameter implements IteratorAggregate
{
    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * @return array
     */
    public function getRealParameters()
    {
        $array      = [];
        $obj        = new ReflectionObject($this);
        $properties = $obj->getProperties();

        foreach ($properties as $property) {
            $docComment  = $property->getDocComment();
            $key         = trim(Helper::findFromString($docComment, '@real', "\n"));
            $value       = $property->getValue($this);
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getRealParameters();
    }
}
