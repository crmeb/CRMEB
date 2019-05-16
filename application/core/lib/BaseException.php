<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/16 9:18
 */

namespace app\core\lib;

/*
 * 错误处理基类
 * */
class BaseException
{
    //错误提示
    public $msg='系统错误';
    //HTTP状态码
    public $code=500;
    //自定义错误代码
    public $errorCode=400;


}