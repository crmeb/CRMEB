<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/12 17:00
 */

namespace app\core\model;

use traits\ModelTrait;
use basic\ModelBasic;

/*
 * Api接口列表
 * class ApiMenus
 * */
class ApiMenus extends ModelBasic
{
    use ModelTrait;

    /*
     * 接口列表配置
     *
     * */
    protected $hash=[

    ];

    /*
     * 获取
     * */
    public static function getHash($name)
    {

    }

}