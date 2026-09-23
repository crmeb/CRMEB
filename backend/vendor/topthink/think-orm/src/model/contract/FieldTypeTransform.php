<?php

declare (strict_types = 1);

namespace think\model\contract;

use think\model\contract\Modelable as Model;

interface FieldTypeTransform
{
    public static function get(mixed $value, Model $model): ?static;

    /**
     * @return static|mixed
     */
    public static function set($value, Model $model) : mixed;
}
