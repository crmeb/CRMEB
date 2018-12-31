<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/24
 */

namespace service;


use think\Exception;
use think\Hook;
use think\Loader;

class HookService
{

    /**
     * 监听有返回结果的行为
     * @param $tag
     * @param $params
     * @param null $extra
     * @param bool $once
     * @return mixed
     */
    public static function resultListen($tag, $params, $extra = null, $once = false,$behavior = null)
    {
            self::beforeListen($tag,$params,$extra,false,$behavior);
        return self::listen($tag,$params,$extra,$once,$behavior);
    }

    /**
     * 监听后置行为
     * @param $tag
     * @param $params
     * @param null $extra
     */
    public static function afterListen($tag, $params, $extra = null, $once = false, $behavior = null)
    {
        try{
            return self::listen($tag.'_after',$params,$extra,$once,$behavior);
        }catch (\Exception $e){}
    }

    public static function beforeListen($tag,$params,$extra = null, $once = false, $behavior = null)
    {
        try{
            return self::listen($tag.'_before',$params,$extra,$once,$behavior);
        }catch (\Exception $e){}
    }

    /**
     * 监听行为
     * @param $tag
     * @param $params
     * @param null $extra
     * @param bool $once
     * @return mixed
     */
    public static function listen($tag, $params, $extra = null, $once = false, $behavior = null)
    {
        if($behavior && method_exists($behavior,Loader::parseName($tag,1,false))) self::add($tag,$behavior);
        return Hook::listen($tag,$params,$extra,$once);
    }

    /**
     * 添加前置行为
     * @param $tag
     * @param $behavior
     * @param bool $first
     */
    public static function addBefore($tag, $behavior, $first = false)
    {
        self::add($tag.'_before',$behavior,$first);
    }

    /**
     * 添加后置行为
     * @param $tag
     * @param $behavior
     * @param bool $first
     */
    public static function addAfter($tag, $behavior, $first = false)
    {
        self::add($tag.'_after',$behavior,$first);
    }

    /**
     * 添加行为
     * @param $tag
     * @param $behavior
     * @param bool $first
     */
    public static function add($tag, $behavior, $first = false)
    {
        Hook::add($tag,$behavior,$first);
    }

}