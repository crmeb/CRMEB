<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

if (!function_exists('class_basename')) {
    /**
     * 获取类名(不包含命名空间)
     *
     * @param  string|object $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     *获取一个类里所有用到的trait，包括父类的
     *
     * @param $class
     * @return array
     */
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_merge([$class => $class], class_parents($class)) as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * 获取一个trait里所有引用到的trait
     *
     * @param  string $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}
if (!function_exists('classnames')) {
    /**
     * css样式名生成器
     * classnames("foo", "bar"); // => "foo bar"
     * classnames("foo", [ "bar"=> true ]); // => "foo bar"
     * classnames([ "foo-bar"=> true ]); // => "foo-bar"
     * classnames([ "foo-bar"=> false ]); // => "
     * classnames([ "foo" => true ], [ "bar"=> true ]); // => "foo bar"
     * classnames([ "foo" => true, "bar"=> true ]); // => "foo bar"
     * classnames("foo", [ "bar"=> true, "duck"=> false ], "baz", [ "quux"=> true ]); // => "foo bar baz quux"
     * classnames(null, false, "bar", 0, 1, [ "baz"=> null ]); // => "bar 1"
     */
    function classnames()
    {
        $args    = func_get_args();
        $classes = array_map(function ($arg) {
            if (is_array($arg)) {
                return implode(" ", array_filter(array_map(function ($expression, $class) {
                    return $expression ? $class : false;
                }, $arg, array_keys($arg))));
            }
            return $arg;
        }, $args);
        return implode(" ", array_filter($classes));
    }
}