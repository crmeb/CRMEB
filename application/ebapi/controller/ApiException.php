<?php

namespace app\ebapi\controller;


use app\core\util\ApiLogs;
use Exception;
use service\JsonService;
use think\exception\Handle;
use think\exception\ValidateException;
use think\exception\PDOException;
use think\exception\ErrorException;
use think\exception\HttpException;
use think\exception\DbException;
use app\core\lib\BaseException;

class ApiException extends Handle
{
    public function render(Exception $e){
        //记录错误日志
        if ($e instanceof \think\Exception) ApiLogs::recodeErrorLog($e);
        //可以在此交由系统处理
        if(\think\Config::get('app_debug')) return Handle::render($e);
        //参数验证错误
        if ($e instanceof ValidateException) return JsonService::fail($e->getError(),[], $e->getCode());
        //数据库错误
        if($e instanceof PDOException) return JsonService::fail($e->getMessage(),[],$e->getCode());
        //Db错误
        if($e instanceof DbException) return JsonService::fail($e->getMessage(),[],$e->getCode());
        //HTTP相应错误
        if($e instanceof HttpException) return JsonService::fail($e->getMessage(),[],$e->getCode());
        //其他错误异常
        if($e instanceof ErrorException) return JsonService::fail($e->getMessage(),[],$e->getCode());
        //默认错误提示
        $baseExcep=new BaseException();
        return JsonService::fail($baseExcep->msg,[$e->getMessage(),$e->getFile()],$baseExcep->code);
    }
}